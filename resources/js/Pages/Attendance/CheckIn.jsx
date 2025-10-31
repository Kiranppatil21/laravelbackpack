import React, {useState, useRef} from 'react';

export default function CheckIn({employeeId, tenantUuid}){
  const [status, setStatus] = useState(null);
  const [qrCode, setQrCode] = useState('');
  const [coords, setCoords] = useState(null);
  const videoRef = useRef(null);

  const handleCheckIn = async (extra = {}) => {
    setStatus('sending');
    try {
      const payload = Object.assign({employee_id: employeeId, tenant_uuid: tenantUuid, check_in_type: 'manual'}, extra);
      const res = await fetch('/api/attendance/checkin', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(payload)
      });
      if (!res.ok) throw new Error('Checkin failed');
      setStatus('checked-in');
    } catch (err) {
      setStatus('error');
    }
  };

  const handleCheckOut = async () => {
    setStatus('sending');
    try {
      const res = await fetch('/api/attendance/checkout', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({employee_id: employeeId, tenant_uuid: tenantUuid})
      });
      if (!res.ok) throw new Error('Checkout failed');
      setStatus('checked-out');
    } catch (err) {
      setStatus('error');
    }
  };

  const useGeolocation = () => {
    if (!navigator.geolocation) {
      alert('Geolocation not supported in this browser');
      return;
    }

    navigator.geolocation.getCurrentPosition((position) => {
      const p = {lat: position.coords.latitude, lng: position.coords.longitude};
      setCoords(p);
    }, (err) => {
      alert('Unable to access location: ' + err.message);
    }, {timeout: 10000});
  };

  const handleCheckInWithGeo = () => {
    if (!coords) return alert('No coordinates available. Click "Use Geolocation" first.');
    handleCheckIn({check_in_type: 'geo', check_in_meta: {lat: coords.lat, lng: coords.lng}});
  };

  // Simple QR input fallback; try to use BarcodeDetector API if available
  const startCameraScan = async () => {
    if (!('BarcodeDetector' in window)) {
      return alert('Camera scanning not available in this browser. Please paste QR code string.');
    }

    try {
      const stream = await navigator.mediaDevices.getUserMedia({video: {facingMode: 'environment'}});
      if (videoRef.current) videoRef.current.srcObject = stream;
      const detector = new BarcodeDetector({formats: ['qr_code']});

      const scanLoop = async () => {
        try {
          const results = await detector.detect(videoRef.current);
          if (results && results.length) {
            const code = results[0].rawValue;
            setQrCode(code);
            // stop tracks
            stream.getTracks().forEach(t => t.stop());
          } else {
            requestAnimationFrame(scanLoop);
          }
        } catch (e) {
          console.error('scan error', e);
        }
      };

      requestAnimationFrame(scanLoop);
    } catch (e) {
      alert('Unable to access camera: ' + e.message);
    }
  };

  const handleCheckInWithQr = () => {
    if (!qrCode) return alert('No QR code scanned or entered');
    handleCheckIn({check_in_type: 'qr', check_in_meta: {qr: qrCode}});
  };

  return (
    <div>
      <h1>Attendance</h1>
      <p>Status: {status}</p>

      <div style={{marginBottom: 12}}>
        <button onClick={() => handleCheckIn()}>Check In (manual)</button>
        <button onClick={handleCheckOut}>Check Out</button>
      </div>

      <div style={{marginBottom: 12}}>
        <h3>Geolocation</h3>
        <button onClick={useGeolocation}>Use Geolocation</button>
        <button onClick={handleCheckInWithGeo}>Check In With Geo</button>
        <div>Coords: {coords ? `${coords.lat}, ${coords.lng}` : 'none'}</div>
      </div>

      <div style={{marginBottom: 12}}>
        <h3>QR Code</h3>
        <div>
          <input value={qrCode} onChange={e => setQrCode(e.target.value)} placeholder="Paste or scan QR code" />
          <button onClick={handleCheckInWithQr}>Check In With QR</button>
        </div>
        <div style={{marginTop:8}}>
          <button onClick={startCameraScan}>Start Camera Scan (if supported)</button>
        </div>
        <div style={{marginTop:8}}>
          <video ref={videoRef} style={{width: '320px', height: '240px'}} autoPlay muted playsInline></video>
        </div>
      </div>
    </div>
  );
}
