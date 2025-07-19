# Greyline Studio - Professional Web Development Website

A modern, secure, and responsive website for Greyline Studio, a web development company. Built with HTML5, CSS3, and vanilla JavaScript with advanced security features to prevent bot spam and protect against common web vulnerabilities.

## 🚀 Features

### Design & User Experience
- **Modern Dark Theme**: Professional dark color scheme with gradient accents
- **Fully Responsive**: Optimized for all devices (desktop, tablet, mobile)
- **Smooth Animations**: Scroll-triggered animations and interactive elements
- **Professional Typography**: Clean, readable fonts with proper hierarchy
- **Interactive Elements**: Hover effects, smooth scrolling, and engaging UI

### Security Features
- **Anti-Bot Protection**: Honeypot fields and rate limiting
- **Input Sanitization**: Prevents XSS and injection attacks
- **Form Validation**: Client-side validation with user-friendly error messages
- **Security Headers**: Meta tags for enhanced security
- **Rate Limiting**: Prevents spam submissions

### Performance
- **Optimized Loading**: Efficient CSS and JavaScript
- **Smooth Scrolling**: 60fps animations and transitions
- **Lazy Loading**: Images and content load as needed
- **Minimal Dependencies**: Only essential external libraries

## 📁 File Structure

```
greyline/
├── index.html          # Main HTML file
├── styles.css          # CSS styles and responsive design
├── script.js           # JavaScript functionality and security
└── README.md           # This file
```

## 🛠️ Setup Instructions

### Local Development

1. **Clone or Download** the project files to your local machine

2. **Open the Website**:
   - Double-click `index.html` to open in your browser
   - Or use a local server for better development experience

3. **Using a Local Server** (Recommended):
   ```bash
   # Using Python 3
   python -m http.server 8000
   
   # Using Node.js (if you have http-server installed)
   npx http-server
   
   # Using PHP
   php -S localhost:8000
   ```

4. **Access the Website**:
   - Open your browser and go to `http://localhost:8000`

### Production Deployment

#### Option 1: Static Hosting (Recommended)
- **Netlify**: Drag and drop the folder to Netlify
- **Vercel**: Connect your repository to Vercel
- **GitHub Pages**: Push to GitHub and enable Pages
- **AWS S3**: Upload files to S3 bucket with static hosting

#### Option 2: Traditional Web Hosting
1. Upload all files to your web server's public directory
2. Ensure `index.html` is in the root directory
3. Configure your domain to point to the hosting

## 🔒 Security Features Explained

### Anti-Bot Protection
- **Honeypot Field**: Hidden form field that bots fill out but humans don't see
- **Rate Limiting**: Prevents multiple submissions within a short time period
- **Input Validation**: Checks for valid email formats and minimum content length

### XSS Prevention
- **Input Sanitization**: Removes potentially dangerous HTML and JavaScript
- **Content Security**: Meta headers prevent content injection
- **Form Validation**: Server-side validation should be implemented for production

### Additional Security Measures
- **Security Headers**: X-Frame-Options, X-Content-Type-Options, etc.
- **Referrer Policy**: Controls information sent in referrer headers
- **Input Length Limits**: Prevents oversized submissions

## 🎨 Customization

### Colors
The website uses CSS custom properties (variables) for easy color customization:

```css
:root {
    --primary-color: #6366f1;    /* Main brand color */
    --secondary-color: #10b981;  /* Accent color */
    --bg-primary: #0f172a;       /* Main background */
    --bg-secondary: #1e293b;     /* Secondary background */
    --text-primary: #ffffff;     /* Main text color */
    --text-secondary: #d1d5db;   /* Secondary text color */
}
```

### Content
- **Company Information**: Update contact details, about section, and portfolio
- **Images**: Replace placeholder images with your actual project screenshots
- **Services**: Modify the services offered to match your business
- **Portfolio**: Add your actual projects with descriptions and technologies used

### Contact Form
The contact form includes security features but needs backend integration:

1. **For Production**: Implement server-side form processing
2. **Email Integration**: Connect to email service (SendGrid, Mailgun, etc.)
3. **Database Storage**: Store form submissions in a database
4. **Additional Security**: Add CAPTCHA, reCAPTCHA, or similar services

## 📱 Responsive Design

The website is fully responsive with breakpoints at:
- **Desktop**: 1200px and above
- **Tablet**: 768px - 1199px
- **Mobile**: Below 768px
- **Small Mobile**: Below 480px

## 🔧 Browser Support

- **Modern Browsers**: Chrome, Firefox, Safari, Edge (latest versions)
- **Mobile Browsers**: iOS Safari, Chrome Mobile, Samsung Internet
- **Fallbacks**: Graceful degradation for older browsers

## 🚀 Performance Optimization

### Current Optimizations
- **Minimal JavaScript**: Only essential functionality
- **Efficient CSS**: Optimized selectors and minimal redundancy
- **Fast Loading**: No heavy frameworks or libraries
- **Optimized Images**: Use WebP format when possible

### Further Optimizations
- **Image Compression**: Compress images for faster loading
- **CDN**: Use a CDN for external resources
- **Caching**: Implement browser caching headers
- **Minification**: Minify CSS and JavaScript for production

## 📞 Contact Form Backend Integration

For production use, you'll need to implement server-side form processing:

### Example with Node.js/Express
```javascript
const express = require('express');
const nodemailer = require('nodemailer');
const app = express();

app.post('/contact', async (req, res) => {
    // Validate input
    // Send email
    // Store in database
    // Return response
});
```

### Example with PHP
```php
<?php
if ($_POST) {
    // Validate input
    // Sanitize data
    // Send email
    // Store in database
    // Return JSON response
}
?>
```

## 🔍 SEO Optimization

The website includes:
- **Meta Tags**: Title, description, keywords
- **Open Graph**: Social media sharing optimization
- **Semantic HTML**: Proper heading structure and semantic elements
- **Alt Text**: Image descriptions for accessibility
- **Structured Data**: Ready for schema markup implementation

## 📈 Analytics Integration

To add analytics:
1. **Google Analytics**: Add tracking code to `<head>` section
2. **Google Tag Manager**: Implement GTM for advanced tracking
3. **Custom Events**: Track form submissions and user interactions

## 🛡️ Additional Security Recommendations

1. **HTTPS**: Always use HTTPS in production
2. **CSP Headers**: Implement Content Security Policy
3. **Regular Updates**: Keep dependencies updated
4. **Monitoring**: Set up security monitoring and alerts
5. **Backup**: Regular backups of your website

## 📄 License

This project is created for Greyline Studio. Customize and use as needed for your business.

## 🤝 Support

For questions or customization requests:
- Email: hello@greyline.studio
- Update the contact information in the HTML file with your actual details

---

**Built with ❤️ for Greyline Studio** 