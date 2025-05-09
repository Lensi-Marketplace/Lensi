# LenSi - Freelance Marketplace Platform

**LenSi** is a modern freelance marketplace platform connecting talented freelancers with clients seeking quality services.  
Built with a custom PHP MVC framework, LenSi offers a clean design and seamless user experience for both freelancers and clients.

---

## 🌟 Features

### For Clients
- **Post Jobs**: Create detailed job postings with custom requirements  
- **Discover Talent**: Browse freelancer profiles and portfolios  
- **Secure Payments**: Protected payment processing system  
- **Project Management**: Track ongoing projects and communicate with freelancers  

### For Freelancers
- **Service Listings**: Create professional service offerings  
- **Job Discovery**: Search and apply for relevant job postings  
- **Portfolio Management**: Showcase your work and skills  
- **Payment Protection**: Secure payment receiving system  

### Platform Features
- **User Authentication**: Email/password registration and login, GitHub OAuth integration  
- **Role-based Access Control**  
- **Communication Tools**:  
  - Real-time messaging system  
  - Notification center  
  - File sharing capabilities  
- **Community**:  
  - Discussion forums  
  - Events calendar  
  - Resource sharing  
  - Professional groups  
- **Dashboard Analytics**:  
  - Visualize earnings  
  - Track project progress  
  - Monitor profile performance  

---

## 🛠️ Technology Stack

- **Backend**: Custom PHP MVC framework  
- **Frontend**: HTML5, CSS3, JavaScript  
- **Database**: MySQL  
- **Authentication**: Traditional login + GitHub OAuth  
- **UI Framework**: Bootstrap 5  
- **Icons**: Font Awesome 6  
- **Data Visualization**: Chart.js  
- **Animations**: GSAP  

---

## 📁 Project Structure

.htaccess # URL rewriting rules
index.php # Application entry point

app/
├── config/ # Configuration files
├── controllers/ # Controller classes
├── core/ # Core framework files
├── helpers/ # Helper functions and utilities
├── models/ # Database models
├── sql/ # SQL schema and sample data
└── views/ # View templates

public/
├── css/ # Stylesheets
├── images/ # Static images
├── js/ # JavaScript files
└── uploads/ # User uploaded content


---

## 🚀 Installation

**Clone the repository:**

bash :
git clone https://github.com/yourusername/lensi.git

Configure your web server (Apache/Nginx) to point to the project directory

Import the database schema:
mysql -u username -p database_name < app/sql/setup.sql

Configure the application:
Rename app/config/config.example.php to config.php
Update database credentials and other settings

Set proper permissions:
chmod 755 -R public/uploads/

Fork the repository

Create your feature branch:

bash
Copier
Modifier
git checkout -b feature/amazing-feature
Commit your changes:

bash
Copier
Modifier
git commit -m 'Add some amazing feature'
Push to the branch:

bash
Copier
Modifier
git push origin feature/amazing-feature
Open a pull request

📄 License
This project is licensed under the MIT License – see the LICENSE file for details.

🙏 Acknowledgements
Bootstrap

Font Awesome

Chart.js

GSAP Animation

yaml
Copier
Modifier

---

Let me know if you’d like a French version or want badges (like build status or license) added at the top.
