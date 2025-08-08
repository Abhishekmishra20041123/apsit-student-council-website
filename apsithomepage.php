<?php
// Include the announcements fetching script
include 'fetch_announcements.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>APSIT Student Council</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style type="text/css">
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f5f5;
            color: #333;
            padding-top: 60px; /* Adjust based on navbar height */
        }

        /* News Ticker */
        .news-container {
            display: flex;
            height: 50px;
            background-color: #ffffff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-top: 13px;
        }
        .latest-news-label {
            background-color: #05055c; /* Navy */
            color: white;
            display: inline-block;
            align-items: center;
            justify-content: center;
            padding: 0 15px;
            font-weight: bold;
            font-size: 16px;
        }
        .news-content {
            flex-grow: 1;
            background-color: #007bff; /* Bright blue */
            display: flex;
            align-items: center;
            padding-left: 15px;
            color: white;
            overflow: hidden;
        }
        .logo-placeholder {
            width: 50px;
            height: 25px;
            background-color: rgb(248, 248, 8);
            border-radius: 20px;
            margin: 5px;
            display: inline-block;
            align-items: center;
            justify-content: center;
            font-size: 600;
            font-weight: bold;
            color: rgb(255, 0, 0);
            text-align: center;
        }
        /* Carousel Customization */
        .carousel-item {
            height: 400px;
        }
        .carousel-item img {
            object-fit: cover;
            height: 100%;
        }
        .carousel-caption {
            background-color: rgba(0,0,0,0.5);
            padding: 15px;
            border-radius: 5px;
        }

        /* Council Member Cards */
        /* Council Member Cards - Updated Style */
.council-card {
    border: none;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
}

.council-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.15);
}

.council-card .card-img-top {
    transition: all 0.5s ease;
}

.council-card:hover .card-img-top {
    transform: scale(1.05);
}

.council-card .card-body {
    padding: 1rem;
    background: #ffffff;
    transition: all 0.3s ease;
}

.council-card:hover .card-body {
    background: #f8f9fa;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .council-card .card-img-top {
        height: 180px;
    }
}

@media (max-width: 576px) {
    .council-card .card-img-top {
        height: 160px;
    }
}

        /* Upcoming Events Section */
        .event-card {
            border-left: 4px solid #007bff;
            transition: transform 0.3s ease;
        }
        .event-card:hover {
            transform: translateX(5px);
        }
        .event-date {
            background-color: #000080;
            color: white;
            padding: 10px;
            text-align: center;
            border-radius: 5px;
        }
        .event-date .day {
            font-size: 24px;
            font-weight: bold;
        }
        .event-date .month {
            font-size: 14px;
        }

        /* Achievements Section */
        .achievement-card {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .achievement-card:hover {
            transform: scale(1.03);
        }
        .achievement-icon {
            font-size: 40px;
            color: #007bff;
        }

        /* Testimonials Section */
        .testimonial-card {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .testimonial-img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #007bff;
        }
        .quote-icon {
            font-size: 30px;
            color: #007bff;
            opacity: 0.5;
        }

        /* Contact Form */
        .contact-form {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .contact-info-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        .contact-icon {
            width: 40px;
            height: 40px;
            background-color: #007bff;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }

        /* Footer */
        footer {
            background-color: #212529;
            color: white;
            padding: 40px 0 20px;
        }
        .social-icon {
            width: 40px;
            height: 40px;
            background-color: #3B5998;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 10px;
            font-size: 18px;
            transition: transform 0.3s ease;
        }
        .social-icon:hover {
            transform: scale(1.1);
        }
        .social-icon.instagram {
            background: #d6249f;
            background: radial-gradient(circle at 30% 107%, #fdf497 0%, #fdf497 5%, #fd5949 45%,#d6249f 60%,#285AEB 90%);
        }
        .social-icon.twitter {
            background-color: #1DA1F2;
        }
        .social-icon.youtube {
            background-color: #FF0000;
        }

        /* Back to Top Button */
        .back-to-top {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 40px;
            height: 40px;
            background-color: #007bff;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 1000;
        }
        .back-to-top.visible {
            opacity: 1;
        }

        /* Animations */
        .fade-in {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.5s ease, transform 0.5s ease;
        }
        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .carousel-item {
                height: 300px;
            }
            .event-date {
                margin-bottom: 15px;
            }
        }

        .navbar {
            background-color: #050505;
            padding: 0.5rem 2rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            margin-left: -90px; /* Shift navbar 15px to the left */
        }

        .container {
            padding-left: 15px; /* Compensate for the negative margin */
        }

        .navbar-brand img {
            height: 35px;
            width: 35px;
        }

        .navbar-text {
            color: #ffffff;
            font-size: 1rem;
            font-weight: 500;
            margin-left: 1rem;
        }

        .nav-link {
            color: #ffffff;
            font-weight: 500;
            padding: 0.5rem 1rem; /* Increased padding by 3px (from 0.4rem to 0.5rem) */
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            color: #4cc9f0;
            transform: translateY(-2px);
        }

        .btn-outline-light {
            border-color: #ffffff;
            color: #ffffff;
            padding: 0.5rem 1rem; /* Increased padding by 3px (from 0.4rem to 0.5rem) */
            transition: all 0.3s ease;
        }

        .btn-outline-light:hover {
            background-color: #ffffff;
            color: #0a0a0a;
            transform: translateY(-2px);
        }
    </style>
</head>

<body>
    <!-- Navigation Bar -->
    <div class="sticky-top">
        <nav class="navbar navbar-expand-xl bg-dark navbar-dark fixed-top">
            <div class="container">
                <a class="navbar-brand" href="#">
                    <img src="Untitled design.png" alt="APSIT_logo" style="height:45px; width:45px">
                </a>
                
                <span class="col-sm-6 navbar-text text-white">A. P. Shah Institute of Technology, Mumbai</span>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsenavbar">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="collapsenavbar">
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item dropdown">
                            <button type="button" class="btn btn-dark dropdown-toggle" data-toggle="dropdown">Student Council</button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item text-info" href="Meet the president\president.html">Message from the President</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-info" href="Meet the representatives\representatives.html">Meet the Representatives</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-info" href="Meeting/meet.php">Minutes of Meetings</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-info" href="Letter/letters_to_admin.html">Letters to Administration</a>
                            </div>
                        </li>

                        <li class="nav-item dropdown">
                            <button type="button" class="btn btn-dark dropdown-toggle" data-toggle="dropdown">Announcements</button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item text-info" href="Announcements/announcements.php">New Announcements</a>
                                <div class="dropdown-divider"></div>
                            </div>
                        </li>

                        <li class="nav-item dropdown">
                            <button type="button" class="btn btn-dark dropdown-toggle" data-toggle="dropdown">Events</button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item text-info" href="Events/events.html">Events</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-info" href="Calendar/calendar.html">Calendar</a>
                            </div>
                        </li>

                        <li class="nav-item dropdown">
                            <button type="button" class="btn btn-dark dropdown-toggle" data-toggle="dropdown">Student's Life</button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item text-info" href="Student life/studentlife.html">APSIT Life</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-info" href="Achivements/achivements.html">Achivements</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-info" href="resource/resource.php">Resources</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-info" href="faq.html">FAQs</a>
                            </div>
                        </li>

                        <li class="nav-item dropdown">
                            <button type="button" class="btn btn-dark dropdown-toggle" data-toggle="dropdown">Login</button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item text-info" href="Admin/admin_login.php">Admin</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-info" href="Profile/profile.html">Member</a>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- News Ticker -->
        <div class="news-container">
            <div class="latest-news-label">LATEST ANNOUNCEMENTS</div>
            <div class="news-content">
                <marquee class="marquee-content" behavior="scroll" direction="left" id="announcements-marquee">
                    <?php
                    if (!empty($announcements)) {
                        foreach ($announcements as $announcement) {
                            echo '<span class="logo-placeholder">NEW</span> ';
                            echo htmlspecialchars($announcement['title']) . ' | ';
                        }
                    } else {
                        echo '<span class="logo-placeholder">INFO</span> No announcements available at this time';
                    }
                    ?>
                </marquee>
            </div>
        </div>
    </div>


    <!-- Carousel/Slider -->
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div id="mainCarousel" class="carousel slide" data-ride="carousel">
                    <ol class="carousel-indicators">
                        <li data-target="#mainCarousel" data-slide-to="0" class="active"></li>
                        <li data-target="#mainCarousel" data-slide-to="1"></li>
                        <li data-target="#mainCarousel" data-slide-to="2"></li>
                    </ol>
                    <div class="carousel-inner rounded shadow">
                        <div class="carousel-item active">
                            <img src="banner1.png" class="d-block w-100" alt="Student Council">
                            <div class="carousel-caption d-none d-md-block">
                                <h3>Student Council 2023-24</h3>
                                <p>Working together for a better campus experience</p>
                            </div>
                        </div>
                        <div class="carousel-item">
                            <img src="banner3.jpg" class="d-block w-100" alt="Campus Events">
                            <div class="carousel-caption d-none d-md-block">
                                <h3>Campus Events</h3>
                                <p>Exciting activities throughout the academic year</p>
                            </div>
                        </div>
                        <div class="carousel-item">
                            <img src="banner2.jpg" class="d-block w-100" alt="Student Activities">
                            <div class="carousel-caption d-none d-md-block">
                                <h3>Student Activities</h3>
                                <p>Developing skills beyond the classroom</p>
                            </div>
                        </div>
                    </div>
                    <a class="carousel-control-prev" href="#mainCarousel" role="button" data-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="sr-only">Previous</span>
                    </a>
                    <a class="carousel-control-next" href="#mainCarousel" role="button" data-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="sr-only">Next</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Council Members Section -->
<div class="container mt-5">
    <div class="row">
        <div class="col-12 mb-4">
            <div class="d-flex align-items-center">
                <div class="bg-primary p-3 rounded-circle mr-3">
                    <i class="material-icons text-white">people</i>
                </div>
                <h2 class="mb-0">Council Members</h2>
            </div>
            <hr>
        </div>
    </div>
    <div class="row justify-content-center">
        <!-- President Card -->
        <div class="col-md-4 col-sm-6 mb-4">
            <div class="card council-card h-100">
                <div class="position-relative">
                    <img src="razzaq.jpeg" class="card-img-top" alt="President" style="height: 200px; object-fit: cover;">
                    <div class="position-absolute w-100 text-center" style="bottom: 0; background: rgba(0,0,0,0.7);">
                        <h6 class="text-white py-1 mb-0">PRESIDENT</h6>
                    </div>
                </div>
                <div class="card-body text-center py-2">
                    <h6 class="card-title text-primary mb-0">RAZZAQ SHIKALGAR</h6>
                    <p class="card-text text-muted small">Leading with Vision</p>
                </div>
            </div>
        </div>
        
        <!-- General Secretary Card -->
        <div class="col-md-4 col-sm-6 mb-4">
            <div class="card council-card h-100">
                <div class="position-relative">
                    <img src="ansh.jpeg" class="card-img-top" alt="General Secretary" style="height: 200px; object-fit: cover;">
                    <div class="position-absolute w-100 text-center" style="bottom: 0; background: rgba(0,0,0,0.7);">
                        <h6 class="text-white py-1 mb-0">GENERAL SECRETARY</h6>
                    </div>
                </div>
                <div class="card-body text-center py-2">
                    <h6 class="card-title text-primary mb-0">ANSH CHAVAN</h6>
                    <p class="card-text text-muted small">Organizing Excellence</p>
                </div>
            </div>
        </div>
        
        <!-- Cultural Secretary Card -->
        <div class="col-md-4 col-sm-6 mb-4">
            <div class="card council-card h-100">
                <div class="position-relative">
                    <img src="dravesh.jpeg" class="card-img-top" alt="Cultural Secretary" style="height: 200px; object-fit: cover;">
                    <div class="position-absolute w-100 text-center" style="bottom: 0; background: rgba(0,0,0,0.7);">
                        <h6 class="text-white py-1 mb-0">CULTURAL SECRETARY</h6>
                    </div>
                </div>
                <div class="card-body text-center py-2">
                    <h6 class="card-title text-primary mb-0">DARVESH BARODYA</h6>
                    <p class="card-text text-muted small">Fostering Creativity</p>
                </div>
            </div>
        </div>
        
        <!-- Sports Secretary Card -->
        <div class="col-md-4 col-sm-6 mb-4">
            <div class="card council-card h-100">
                <div class="position-relative">
                    <img src="dhruv.jpeg" class="card-img-top" alt="Sports Secretary" style="height: 200px; object-fit: cover;">
                    <div class="position-absolute w-100 text-center" style="bottom: 0; background: rgba(0,0,0,0.7);">
                        <h6 class="text-white py-1 mb-0">SPORTS SECRETARY</h6>
                    </div>
                </div>
                <div class="card-body text-center py-2">
                    <h6 class="card-title text-primary mb-0">DHRUV SAVANT</h6>
                    <p class="card-text text-muted small">Championing Athletics</p>
                </div>
            </div>
        </div>
        
        <!-- Ladies Representative Card -->
        <div class="col-md-4 col-sm-6 mb-4">
            <div class="card council-card h-100">
                <div class="position-relative">
                    <img src="richa.jpeg" class="card-img-top" alt="Ladies Representative" style="height: 200px; object-fit: cover;">
                    <div class="position-absolute w-100 text-center" style="bottom: 0; background: rgba(0,0,0,0.7);">
                        <h6 class="text-white py-1 mb-0">LADIES REPRESENTATIVE</h6>
                    </div>
                </div>
                <div class="card-body text-center py-2">
                    <h6 class="card-title text-primary mb-0">RICHA THANEKAR</h6>
                    <p class="card-text text-muted small">Empowering Diversity</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- View All Council Members Button -->
    <div class="text-center mt-3 mb-4">
        <a href="Meet the representatives/representatives.html" class="btn btn-outline-primary">Meet All Council Members</a>
    </div>
</div>
    <!-- Upcoming Events Section -->
    <div class="container mt-5 fade-in">
        <div class="row">
            <div class="col-12 mb-4">
                <div class="d-flex align-items-center">
                    <div class="bg-primary p-3 rounded-circle mr-3">
                        <i class="material-icons text-white">event</i>
                    </div>
                    <h2 class="mb-0">Upcoming Events</h2>
                </div>
                <hr>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4 event-card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="event-date">
                                    <div class="day">15</div>
                                    <div class="month">MAR</div>
                                </div>
                            </div>
                            <div class="col-md-9">
                                <h5 class="card-title">Annual Technical Symposium</h5>
                                <p class="card-text">A platform for students to showcase their technical skills and innovations.</p>
                                <div class="d-flex align-items-center">
                                    <i class="material-icons mr-2 text-muted">location_on</i>
                                    <span>Main Auditorium</span>
                                </div>
                                <div class="d-flex align-items-center mt-2">
                                    <i class="material-icons mr-2 text-muted">access_time</i>
                                    <span>9:00 AM - 5:00 PM</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card mb-4 event-card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="event-date">
                                    <div class="day">22</div>
                                    <div class="month">MAR</div>
                                </div>
                            </div>
                            <div class="col-md-9">
                                <h5 class="card-title">Cultural Festival</h5>
                                <p class="card-text">Celebrate diversity through music, dance, and art performances.</p>
                                <div class="d-flex align-items-center">
                                    <i class="material-icons mr-2 text-muted">location_on</i>
                                    <span>Open Air Theatre</span>
                                </div>
                                <div class="d-flex align-items-center mt-2">
                                    <i class="material-icons mr-2 text-muted">access_time</i>
                                    <span>6:00 PM - 10:00 PM</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card mb-4 event-card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="event-date">
                                    <div class="day">05</div>
                                    <div class="month">APR</div>
                                </div>
                            </div>
                            <div class="col-md-9">
                                <h5 class="card-title">Career Development Workshop</h5>
                                <p class="card-text">Learn essential skills for job interviews and resume building.</p>
                                <div class="d-flex align-items-center">
                                    <i class="material-icons mr-2 text-muted">location_on</i>
                                    <span>Seminar Hall</span>
                                </div>
                                <div class="d-flex align-items-center mt-2">
                                    <i class="material-icons mr-2 text-muted">access_time</i>
                                    <span>2:00 PM - 4:00 PM</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card mb-4 event-card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="event-date">
                                    <div class="day">12</div>
                                    <div class="month">APR</div>
                                </div>
                            </div>
                            <div class="col-md-9">
                                <h5 class="card-title">Sports Tournament</h5>
                                <p class="card-text">Inter-department cricket, football, and basketball competitions.</p>
                                <div class="d-flex align-items-center">
                                    <i class="material-icons mr-2 text-muted">location_on</i>
                                    <span>Sports Complex</span>
                                </div>
                                <div class="d-flex align-items-center mt-2">
                                    <i class="material-icons mr-2 text-muted">access_time</i>
                                    <span>10:00 AM - 6:00 PM</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="text-center mt-3">
            <a href="Events/events.html" class="btn btn-outline-primary">View All Events</a>
        </div>
    </div>

    <!-- Latest Achievements Section -->
    <div class="container mt-5 fade-in">
        <div class="row">
            <div class="col-12 mb-4">
                <div class="d-flex align-items-center">
                    <div class="bg-primary p-3 rounded-circle mr-3">
                        <i class="material-icons text-white">emoji_events</i>
                    </div>
                    <h2 class="mb-0">Latest Achievements</h2>
                </div>
                <hr>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="achievement-card">
                    <div class="card-body text-center p-4">
                        <div class="achievement-icon mb-3">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <h5 class="card-title">National Robotics Competition</h5>
                        <p class="card-text">First place in the All India Robotics Challenge 2023</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="achievement-card">
                    <div class="card-body text-center p-4">
                        <div class="achievement-icon mb-3">
                            <i class="fas fa-medal"></i>
                        </div>
                        <h5 class="card-title">Research Publication</h5>
                        <p class="card-text">10 research papers published in international journals</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="achievement-card">
                    <div class="card-body text-center p-4">
                        <div class="achievement-icon mb-3">
                            <i class="fas fa-award"></i>
                        </div>
                        <h5 class="card-title">Hackathon Winners</h5>
                        <p class="card-text">APSIT team won the Smart India Hackathon 2023</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Testimonials Section -->
    <div class="container mt-5 fade-in">
        <div class="row">
            <div class="col-12 mb-4">
                <div class="d-flex align-items-center">
                    <div class="bg-primary p-3 rounded-circle mr-3">
                        <i class="material-icons text-white">format_quote</i>
                    </div>
                    <h2 class="mb-0">Student Testimonials</h2>
                </div>
                <hr>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="testimonial-card">
                    <div class="d-flex align-items-center mb-3">
                        <img src="ansh.jpeg" alt="Student" class="testimonial-img mr-3">
                        <div>
                            <h5 class="mb-0">ANSH</h5>
                            <p class="text-muted mb-0">Computer Engineering, Final Year</p>
                        </div>
                    </div>
                    <div class="position-relative">
                        <i class="fas fa-quote-left quote-icon position-absolute"></i>
                        <p class="pl-4 pt-2">The Student Council has created an amazing environment for learning and growth. The events they organize help us develop both technical and soft skills.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="testimonial-card">
                    <div class="d-flex align-items-center mb-3">
                        <img src="dravesh.jpeg" alt="Student" class="testimonial-img mr-3">
                        <div>
                            <h5 class="mb-0">DARVESH</h5>
                            <p class="text-muted mb-0">Electronics Engineering, Third Year</p>
                        </div>
                    </div>
                    <div class="position-relative">
                        <i class="fas fa-quote-left quote-icon position-absolute"></i>
                        <p class="pl-4 pt-2">Being part of the cultural committee has been a transformative experience. The council provides great support for students to showcase their talents.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Form Section -->
    <div class="container mt-5 mb-5 fade-in">
        <div class="row">
            <div class="col-12 mb-4">
                <div class="d-flex align-items-center">
                    <div class="bg-primary p-3 rounded-circle mr-3">
                        <i class="material-icons text-white">contact_mail</i>
                    </div>
                    <h2 class="mb-0">Contact Us</h2>
                </div>
                <hr>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="contact-form">
                    <form id="contactForm" method="POST">
                        <div class="form-group">
                            <label for="name">Your Name</label>
                            <input type="text" class="form-control" id="name" name="name" placeholder="Enter your name" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
                        </div>
                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <input type="text" class="form-control" id="subject" name="subject" placeholder="Enter subject" required>
                        </div>
                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="5" placeholder="Enter your message" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Send Message</button>
                    </form>
                </div>
            </div>
            <div class="col-md-6">
                <div class="contact-info">
                    <h4 class="mb-4">Get In Touch</h4>
                    <div class="contact-info-item">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Address</h5>
                            <p class="mb-0">A. P. Shah Institute of Technology, Kasarvadavali, Thane, Maharashtra 400615</p>
                        </div>
                    </div>
                    <div class="contact-info-item">
                        <div class="contact-icon">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Phone</h5>
                            <p class="mb-0">+91 22 2597 1234</p>
                        </div>
                    </div>
                    <div class="contact-info-item">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Email</h5>
                            <p class="mb-0">studentcouncil@apsit.edu.in</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3767.2675218972244!2d72.96813!3d19.2236!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3be7b9f0b81f13ad%3A0x19689e197f8e7b96!2sA.%20P.%20Shah%20Institute%20of%20Technology!5e0!3m2!1sen!2sin!4v1646579096754!5m2!1sen!2sin" width="100%" height="250" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Back to Top Button -->
    <div class="back-to-top">
        <i class="fas fa-arrow-up"></i>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h4>APSIT Student Council</h4>
                    <p class="text-muted">The Student Council is the representative body of the students of A. P. Shah Institute of Technology. We work to enhance the student experience and create a vibrant campus community.</p>
                </div>
                <div class="col-lg-4 mb-4">
                    <h4>Quick Links</h4>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-light">Home</a></li>
                        <li><a href="Events/events.html" class="text-light">Events</a></li>
                        <li><a href="Announcements/announcements.html" class="text-light">Announcements</a></li>
                        <li><a href="Contact/contact.html" class="text-light">Contact</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 mb-4">
                    <h4>Connect With Us</h4>
                    <div class="d-flex">
                        <a href="https://www.facebook.com/apsit.official    " class="social-icon facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://www.instagram.com/apsit.official" class="social-icon instagram"><i class="fab fa-instagram"></i></a>
                        <a href="https://www.twitter.com/apsit.official" class="social-icon twitter"><i class="fab fa-twitter"></i></a>
                        <a href="https://www.youtube.com/apsit.official" class="social-icon youtube"><i class="fab fa-youtube"></i></a>
                    </div>
                    <p class="mt-3 text-muted">Subscribe to our newsletter for updates</p>
                    <div class="input-group">
                        <input type="email" class="form-control" placeholder="Your email">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="button">Subscribe</button>
                        </div>
                    </div>
                </div>
            </div>
            <hr class="bg-secondary">
            <div class="text-center py-3">
                <p class="mb-0">&copy; 2023 APSIT Student Council. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://apis.google.com/js/api.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://localhost/MY_PROJECT/oauth2callback"></script>
    
    <script>
        // Back to Top Button
        $(window).scroll(function() {
            if ($(this).scrollTop() > 300) {
                $('.back-to-top').addClass('visible');
            } else {
                $('.back-to-top').removeClass('visible');
            }
        });

        $('.back-to-top').click(function() {
            $('html, body').animate({scrollTop: 0}, 800);
            return false;
        });

        // Fade-in Animation
        $(document).ready(function() {
            checkVisibility();
            $(window).scroll(function() {
                checkVisibility();
            });

            function checkVisibility() {
                $('.fade-in').each(function() {
                    var bottom_of_object = $(this).offset().top + 100;
                    var bottom_of_window = $(window).scrollTop() + $(window).height();
                    if (bottom_of_window > bottom_of_object) {
                        $(this).addClass('visible');
                    }
                });
            }
        });
        function authenticate() {
  return gapi.auth2.getAuthInstance()
    .signIn({
      scope: 'https://www.googleapis.com/auth/gmail.send'
    })
    .then(() => {
      console.log('Sign-in successful');
    })
    .catch(err => {
      console.error('Error signing in', err);
    });
}


    function loadClient() {
        return gapi.client.load('gmail', 'v1')
            .then(() => {
                console.log('GAPI client loaded for Gmail API');
            })
            .catch(err => {
                console.error('Error loading GAPI client', err);
            });
    }

    function sendEmail(name, email, subject, message) {
        const emailContent = [
           'From: me',
          'To: recipient-email@gmail.com',
            'Subject: Test Subject',
             '',
         'Email body text here'
            ].join('\r\n');


        const encodedEmail = btoa(unescape(encodeURIComponent(emailContent)))
            .replace(/\+/g, '-')
            .replace(/\//g, '_')
            .replace(/=+$/, '');

        gapi.client.gmail.users.messages.send({
            userId: 'me',
            resource: {
                raw: encodedEmail
            }
        }).then(() => {
            alert('Thank you for your message! We will get back to you soon.');
            $('#contactForm')[0].reset();
        }).catch(err => {
            console.error('Error sending email:', err);
            alert('There was an error sending your message. Please try again later.');
        });
    }

    function initGAPI() {
        gapi.load('client:auth2', () => {
            gapi.auth2.init({
            client_id: '580461413812-mfj2dpvfbmi6lm18ukhfiorf0sodh7o9.apps.googleusercontent.com',
            cookiepolicy: 'single_host_origin'
            });

        });
    }

    $(document).ready(() => {
        initGAPI();

        $('#contactForm').submit(function(e) {
            e.preventDefault();
            
            // Get form data
            var formData = {
                name: $('#name').val(),
                email: $('#email').val(),
                subject: $('#subject').val(),
                message: $('#message').val()
            };

            // Validate form
            if (!formData.name || !formData.email || !formData.subject || !formData.message) {
                alert('Please fill in all fields');
                return false;
            }

            if (!/^\S+@\S+\.\S+$/.test(formData.email)) {
                alert('Please enter a valid email address');
                return false;
            }

            // Submit form using Ajax
            $.ajax({
                type: 'POST',
                url: 'send_email.php',
                data: formData,
                dataType: 'json',
                encode: true
            })
            .done(function(response) {
                if (response.success) {
                    alert('Thank you for your message! We will get back to you soon.');
                    $('#contactForm')[0].reset();
                } else {
                    alert('There was an error sending your message. Please try again later.');
                }
            })
            .fail(function(xhr, status, error) {
                console.error('Error:', error);
                alert('There was an error sending your message. Please try again later.');
            });
        });
    });

    // Dropdown menu behavior for mobile
    $('.dropdown-toggle').click(function() {
        if ($(window).width() < 992) {
            $(this).next('.dropdown-menu').slideToggle();
        }
    });

    $(window).resize(function() {
        if ($(window).width() >= 992) {
            $('.dropdown-menu').removeAttr('style');
        }
    });
    function refreshAnnouncements() {
            $.ajax({
                url: 'refresh_announcements.php',
                type: 'GET',
                success: function(data) {
                    $('#announcements-marquee').html(data);
                },
                error: function() {
                    console.error('Failed to fetch announcements');
                }
            });
        }
        
        // Refresh announcements every 5 minutes (300000 ms)
        $(document).ready(function() {
            setInterval(refreshAnnouncements, 300000);
        });
        // This script can be included in the homepage to automatically refresh announcements
document.addEventListener("DOMContentLoaded", () => {
    // Initial refresh after page load
    setTimeout(refreshAnnouncements, 5000)
  
    // Set up periodic refresh
    setInterval(refreshAnnouncements, 300000) // Refresh every 5 minutes
  
    function refreshAnnouncements() {
      fetch("fetch_announcements.php", {
        method: "GET",
        headers: {
          "X-Requested-With": "XMLHttpRequest",
        },
      })
        .then((response) => response.json())
        .then((data) => {
          const tickerElement = document.getElementById("announcements-ticker")
          let tickerHtml = ""
  
          if (data.length > 0) {
            data.forEach((announcement) => {
              tickerHtml += `<div class="announcement-item">
                          <span class="logo-placeholder">NEW</span>
                          ${announcement.title}
                      </div>`
            })
          } else {
            tickerHtml += `<div class="announcement-item">
                      <span class="logo-placeholder">INFO</span>
                      No announcements available at this time
                  </div>`
          }
  
          tickerElement.innerHTML = tickerHtml
  
          // Reset animation
          tickerElement.style.animation = "none"
          setTimeout(() => {
            tickerElement.style.animation = "ticker-scroll 30s linear infinite"
          }, 10)
        })
        .catch((error) => {
          console.error("Error refreshing announcements:", error)
        })
    }
  })
    </script>
</body>
</html>