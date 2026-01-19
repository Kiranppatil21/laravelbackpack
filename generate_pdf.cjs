#!/usr/bin/env node
/**
 * SecureServe Demo Guide PDF Generator (Node.js)
 * Uses puppeteer to generate PDF from HTML
 */

const fs = require('fs');
const path = require('path');

async function checkPuppeteer() {
    try {
        const puppeteer = require('puppeteer');
        return puppeteer;
    } catch (error) {
        console.log('📦 Installing puppeteer...');
        const { execSync } = require('child_process');
        try {
            execSync('npm install puppeteer', { stdio: 'inherit' });
            return require('puppeteer');
        } catch (installError) {
            console.error('❌ Failed to install puppeteer:', installError.message);
            return null;
        }
    }
}

async function generatePDF() {
    console.log('🎯 SecureServe Demo Guide PDF Generator (Node.js)');
    console.log('='.repeat(60));
    
    const htmlFile = path.join(__dirname, 'demo-guide.html');
    const pdfFile = path.join(__dirname, 'SecureServe_Demo_Guide.pdf');
    
    // Check if HTML file exists
    if (!fs.existsSync(htmlFile)) {
        console.error(`❌ HTML file not found: ${htmlFile}`);
        return false;
    }
    
    // Load puppeteer
    const puppeteer = await checkPuppeteer();
    if (!puppeteer) {
        return false;
    }
    
    try {
        console.log('🚀 Launching browser...');
        const browser = await puppeteer.launch({ 
            headless: true,
            args: ['--no-sandbox', '--disable-setuid-sandbox']
        });
        
        const page = await browser.newPage();
        
        console.log('📄 Loading HTML file...');
        const htmlContent = fs.readFileSync(htmlFile, 'utf8');
        await page.setContent(htmlContent, { waitUntil: 'networkidle0' });
        
        console.log('🔄 Generating PDF...');
        await page.pdf({
            path: pdfFile,
            format: 'A4',
            margin: {
                top: '2cm',
                right: '2cm',
                bottom: '2cm',
                left: '2cm'
            },
            printBackground: true,
            displayHeaderFooter: true,
            headerTemplate: '<div></div>',
            footerTemplate: `
                <div style="font-size: 10pt; text-align: center; width: 100%; color: #666;">
                    SecureServe Demo Guide • Page <span class="pageNumber"></span> of <span class="totalPages"></span>
                </div>
            `
        });
        
        await browser.close();
        
        // Check file size
        const stats = fs.statSync(pdfFile);
        const fileSizeMB = stats.size / (1024 * 1024);
        
        console.log('✅ PDF generated successfully!');
        console.log(`📁 Location: ${pdfFile}`);
        console.log(`📊 File size: ${fileSizeMB.toFixed(2)} MB`);
        console.log('🎉 Demo guide PDF ready for client presentation!');
        
        return true;
        
    } catch (error) {
        console.error('❌ Error generating PDF:', error.message);
        return false;
    }
}

// Check if running as main module
if (require.main === module) {
    generatePDF().then(success => {
        if (!success) {
            console.log('\n💡 Alternative: Use the browser method:');
            console.log('   python3 open_for_pdf.py');
            process.exit(1);
        }
    }).catch(error => {
        console.error('❌ Unexpected error:', error);
        process.exit(1);
    });
}

module.exports = { generatePDF };