#!/usr/bin/env python3
"""
SecureServe Demo Guide PDF Generator (Alternative Method)
Opens the HTML file in default browser for manual PDF conversion
"""

import os
import webbrowser
from pathlib import Path

def open_html_for_pdf():
    """Open HTML file in browser for manual PDF conversion"""
    current_dir = Path(__file__).parent
    html_file = current_dir / "demo-guide.html"
    
    if not html_file.exists():
        print(f"❌ HTML file not found: {html_file}")
        return False
    
    # Convert to file URL
    file_url = f"file://{html_file.absolute()}"
    
    print("🎯 SecureServe Demo Guide PDF Generator")
    print("=" * 50)
    print(f"📄 Opening HTML file in browser: {html_file}")
    print(f"🌐 URL: {file_url}")
    print()
    print("📋 MANUAL CONVERSION INSTRUCTIONS:")
    print("1. Wait for the browser to open")
    print("2. Press Ctrl+P (Cmd+P on Mac) to open print dialog")
    print("3. Select 'Save as PDF' as destination")
    print("4. Adjust margins if needed (Recommended: Minimum)")
    print("5. Enable 'Background graphics' for colored sections")
    print("6. Save as 'SecureServe_Demo_Guide.pdf'")
    print()
    print("🎨 For best results:")
    print("- Set margins to 'Minimum' or 'None'")
    print("- Enable 'Background graphics' and 'Headers and footers'")
    print("- Use A4 paper size")
    print()
    
    # Open in browser
    try:
        webbrowser.open(file_url)
        print("✅ Browser opened successfully!")
        print("📖 Please follow the instructions above to generate the PDF")
        return True
    except Exception as e:
        print(f"❌ Error opening browser: {e}")
        return False

def main():
    """Main function"""
    success = open_html_for_pdf()
    
    if not success:
        print("\n❌ Failed to open browser. Please manually open:")
        print(f"   {Path(__file__).parent / 'demo-guide.html'}")

if __name__ == "__main__":
    main()