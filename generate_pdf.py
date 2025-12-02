#!/usr/bin/env python3
"""
SecureServe Demo Guide PDF Generator
Converts the HTML demo guide to a professional PDF document
"""

import os
import sys
from pathlib import Path

def install_dependencies():
    """Install required Python packages if not already installed"""
    try:
        import weasyprint
        print("✅ WeasyPrint already installed")
    except ImportError:
        print("📦 Installing WeasyPrint...")
        os.system("pip install weasyprint")
        
def generate_pdf():
    """Convert HTML to PDF using WeasyPrint"""
    try:
        from weasyprint import HTML, CSS
        from weasyprint.text.fonts import FontConfiguration
        
        # File paths
        current_dir = Path(__file__).parent
        html_file = current_dir / "demo-guide.html"
        pdf_file = current_dir / "SecureServe_Demo_Guide.pdf"
        
        if not html_file.exists():
            print(f"❌ HTML file not found: {html_file}")
            return False
            
        print("🔄 Converting HTML to PDF...")
        print(f"📄 Source: {html_file}")
        print(f"📄 Output: {pdf_file}")
        
        # Configure fonts
        font_config = FontConfiguration()
        
        # Additional CSS for better PDF rendering
        pdf_css = CSS(string='''
            @page {
                size: A4;
                margin: 2cm;
                @bottom-center {
                    content: "Page " counter(page) " of " counter(pages);
                    font-size: 10pt;
                    color: #666;
                }
            }
            
            body {
                font-family: "Segoe UI", "Helvetica Neue", Arial, sans-serif;
                line-height: 1.6;
                color: #333;
            }
            
            .cover-page {
                page-break-after: always;
            }
            
            .toc {
                page-break-after: always;
            }
            
            .phase {
                page-break-inside: avoid;
            }
            
            .highlight-box {
                page-break-inside: avoid;
            }
            
            .features-grid {
                page-break-inside: avoid;
            }
            
            .pricing-table {
                page-break-inside: avoid;
            }
            
            h1, h2, h3, h4 {
                page-break-after: avoid;
            }
            
            /* Print-friendly colors */
            .phase {
                background: #f0f0f0 !important;
                color: #333 !important;
                border: 2px solid #667eea;
            }
            
            .phase h2 {
                color: #333 !important;
            }
            
            .cover-page {
                background: #667eea !important;
                color: white !important;
            }
        ''')
        
        # Generate PDF
        html_doc = HTML(filename=str(html_file))
        html_doc.write_pdf(
            str(pdf_file), 
            stylesheets=[pdf_css],
            font_config=font_config
        )
        
        print(f"✅ PDF generated successfully!")
        print(f"📁 Location: {pdf_file}")
        print(f"📊 File size: {pdf_file.stat().st_size / 1024 / 1024:.2f} MB")
        
        return True
        
    except Exception as e:
        print(f"❌ Error generating PDF: {e}")
        return False

def main():
    """Main function"""
    print("🎯 SecureServe Demo Guide PDF Generator")
    print("=" * 50)
    
    # Install dependencies
    install_dependencies()
    
    # Generate PDF
    success = generate_pdf()
    
    if success:
        print("\n🎉 Demo guide PDF ready for client presentation!")
        print("💡 Tip: Test print a few pages to ensure formatting looks good")
    else:
        print("\n❌ PDF generation failed. Please check the errors above.")
        sys.exit(1)

if __name__ == "__main__":
    main()