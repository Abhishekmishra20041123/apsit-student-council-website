# 🚀 GitHub Setup & Deployment Guide

## 📋 Step 1: Create GitHub Repository

### 1.1 Go to GitHub
- Visit [github.com](https://github.com)
- Sign up or log in to your account

### 1.2 Create New Repository
- Click the **"+"** icon in the top right
- Select **"New repository"**
- Fill in the details:
  - **Repository name:** `apsit-student-council-website`
  - **Description:** `APSIT Student Council Website - PHP/MySQL Web Application`
  - **Visibility:** Public (recommended for easy access)
  - **Initialize with:** Check "Add a README file"
- Click **"Create repository"**

## 📤 Step 2: Upload Your Code

### Option A: Using GitHub Desktop (Recommended)

#### 2.1 Install GitHub Desktop
- Download from [desktop.github.com](https://desktop.github.com)
- Install and sign in with your GitHub account

#### 2.2 Clone Repository
- In GitHub Desktop, click **"Clone a repository"**
- Select your `apsit-student-council-website` repository
- Choose a local path (e.g., `C:\GitHub\apsit-student-council-website`)
- Click **"Clone"**

#### 2.3 Copy Your Files
- Copy all files from `C:\xampp\htdocs\MY_PROJECT` to the cloned repository folder
- Exclude these files (they're already in .gitignore):
  - `apsit_website.zip`
  - `temp_part1/`
  - `temp_part2/`

#### 2.4 Commit and Push
- In GitHub Desktop, you'll see all your files
- Add a commit message: `"Initial commit: APSIT Student Council Website"`
- Click **"Commit to main"**
- Click **"Push origin"**

### Option B: Using Git Commands

#### 2.1 Install Git
- Download from [git-scm.com](https://git-scm.com)
- Install with default settings

#### 2.2 Initialize Repository
```bash
cd C:\xampp\htdocs\MY_PROJECT
git init
git add .
git commit -m "Initial commit: APSIT Student Council Website"
git branch -M main
git remote add origin https://github.com/YOUR_USERNAME/apsit-student-council-website.git
git push -u origin main
```

### Option C: Using GitHub Web Interface

#### 2.1 Upload Files
- Go to your repository on GitHub
- Click **"uploading an existing file"**
- Drag and drop your project files
- Add commit message
- Click **"Commit changes"**

## 🌐 Step 3: Deploy from GitHub

### Option A: Deploy to InfinityFree (Free)

#### 3.1 Get GitHub Repository URL
- Go to your repository on GitHub
- Click the green **"Code"** button
- Copy the HTTPS URL: `https://github.com/YOUR_USERNAME/apsit-student-council-website.git`

#### 3.2 Deploy to InfinityFree
- In InfinityFree control panel, look for **"Git Deployment"** or **"Git Integration"**
- Enter your GitHub repository URL
- Set deployment branch to `main`
- Click **"Deploy"**

### Option B: Deploy to Vercel (Free)

#### 3.1 Connect to Vercel
- Go to [vercel.com](https://vercel.com)
- Sign up with GitHub
- Click **"New Project"**
- Import your GitHub repository

#### 3.2 Configure for PHP
- Vercel will auto-detect PHP
- Add build command: `composer install` (if using Composer)
- Set output directory to `/public` (if applicable)

### Option C: Deploy to Netlify (Free)

#### 3.1 Connect to Netlify
- Go to [netlify.com](https://netlify.com)
- Sign up with GitHub
- Click **"New site from Git"**
- Select your repository

#### 3.2 Configure for PHP
- Set build command: `php -S localhost:8000`
- Set publish directory: `/`

## 🔧 Step 4: Database Setup

### 4.1 Import Database
- Use phpMyAdmin in your hosting control panel
- Import `apsit_database_2025-08-08_21-52-22.sql`

### 4.2 Update Configuration
- Edit `config.php` with your hosting database credentials
- Update `deployment_config.php` with your email settings

## 📱 Step 5: Access Your Website

### 5.1 Your Website URL
- **InfinityFree:** `https://yourname.infinityfreeapp.com`
- **Vercel:** `https://your-project.vercel.app`
- **Netlify:** `https://your-project.netlify.app`

### 5.2 Test Features
- ✅ Homepage loads
- ✅ Admin login works
- ✅ Database connection works
- ✅ File uploads work

## 🔄 Step 6: Future Updates

### 6.1 Make Changes Locally
- Edit files in your local project
- Test on localhost first

### 6.2 Push to GitHub
```bash
git add .
git commit -m "Update: [describe your changes]"
git push
```

### 6.3 Auto-Deploy
- Most platforms auto-deploy when you push to GitHub
- Your website updates automatically

## 🛠️ Troubleshooting

### Database Connection Issues
- Check database credentials in `config.php`
- Verify database exists and is accessible
- Test connection locally first

### File Upload Issues
- Check file permissions on hosting
- Verify upload directory exists
- Check file size limits

### Deployment Issues
- Check GitHub repository is public
- Verify repository URL is correct
- Check hosting provider supports PHP

## 📞 Support

### GitHub Help
- [GitHub Docs](https://docs.github.com)
- [GitHub Desktop Guide](https://docs.github.com/en/desktop)

### Deployment Help
- **InfinityFree:** Check their documentation
- **Vercel:** [Vercel Docs](https://vercel.com/docs)
- **Netlify:** [Netlify Docs](https://docs.netlify.com)

---

## 🎉 Success!

Once deployed, your website will be:
- ✅ **Live on the internet**
- ✅ **Accessible from anywhere**
- ✅ **Easy to update**
- ✅ **Professional and reliable**

**Your APSIT Student Council website is now ready for the world! 🌍**
