# Greyline Studio - Professional Web Development Website

A modern, secure, and responsive website for Greyline Studio, a web development company. Built with HTML5, CSS3, and vanilla JavaScript with advanced security features to prevent bot spam and protect against common web vulnerabilities. Features a clean, professional design with full backend integration for contact form processing.

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
- **Clean URLs**: Apache .htaccess configuration for professional URLs

## 📁 File Structure

```
greyline/
├── index.html          # Main HTML file
├── styles.css          # CSS styles and responsive design
├── script.js           # JavaScript functionality and security
├── .htaccess           # Apache configuration for clean URLs
├── backend/
│   └── submit_contact.php  # PHP backend for form processing
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

#### Option 2: Traditional Web Hosting (Current Setup)
1. Upload all files to your web server's public directory
2. Ensure `index.html` is in the root directory
3. Upload the `.htaccess` file for clean URLs
4. Upload the `backend/` folder with your PHP files
5. Configure your domain to point to the hosting
6. Set up your MySQL database for contact form storage

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
- **CORS Configuration**: Proper cross-origin request handling
- **Database Security**: Prepared statements prevent SQL injection

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
- **Company Information**: Update contact details and about section
- **Images**: Replace placeholder images with your actual project screenshots
- **Services**: Modify the services offered to match your business
- **Contact Information**: Update email, phone, and social media links

### Contact Form
The contact form is fully integrated with a PHP backend:

1. **✅ Backend Integration**: PHP script processes form submissions
2. **✅ Database Storage**: MySQL database stores all contact submissions
3. **✅ Security Features**: Honeypot, rate limiting, input sanitization
4. **✅ JSON Responses**: Proper API responses for frontend handling
5. **✅ Job Numbering**: Auto-generates job numbers for tracking

**Database Schema Required:**
```sql
CREATE TABLE contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    job_number VARCHAR(20) NOT NULL,
    project_title VARCHAR(255),
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status VARCHAR(50) DEFAULT 'New',
    notes TEXT,
    subject VARCHAR(255),
    timestamp DATETIME,
    source VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

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

The website includes a complete PHP backend for form processing:

### Current Implementation
- **File**: `backend/submit_contact.php`
- **Database**: MySQL with PDO prepared statements
- **Security**: Input validation, sanitization, and bot protection
- **Response**: JSON format for frontend integration

### Features
- ✅ Auto-generates job numbers (JOB-00001, JOB-00002, etc.)
- ✅ Stores all form data with timestamps
- ✅ Bot protection via honeypot field
- ✅ CORS headers for cross-origin requests
- ✅ Error handling with proper HTTP status codes

### Configuration
Update the database credentials in `backend/submit_contact.php`:
```php
$host = "your_host";
$dbname = "your_database";
$username = "your_username";
$password = "your_password";
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
5. **Backup**: Regular backups of your website and database
6. **Database Security**: Use strong passwords and limit database access
7. **File Permissions**: Set proper file permissions on server

## 📄 License

This project is created for Greyline Studio. Customize and use as needed for your business.

## 🤝 Support

For questions or customization requests:
- Email: greylinestudio@gmail.com
- Phone: +1 (484) 274-3727
- Website: https://greylinestudio.com

## 📝 Recent Updates

- ✅ Removed portfolio section for cleaner design
- ✅ Added complete PHP backend integration
- ✅ Implemented MySQL database storage
- ✅ Added bot protection and security features
- ✅ Created custom X logo for social links
- ✅ Added .htaccess for clean URLs
- ✅ Enhanced form validation and error handling

---

**Built with ❤️ for Greyline Studio** 