# SkyWay Airlines - Flight Booking System

## Overview
SkyWay Airlines is a comprehensive flight booking system that allows users to search for flights, select seats, make payments, and manage their bookings. The system features a modern, responsive design with intuitive user interfaces and smooth animations.

## Features

### User Management
- **User Registration**: New users can create accounts with profile photos
- **User Login**: Secure authentication system
- **User Dashboard**: Personalized dashboard showing booking history and profile information
- **Profile Management**: Users can update their personal information and preferences

### Flight Booking Process
1. **Flight Search**: Search for flights based on destination, date, and class
2. **Seat Selection**: Interactive seat map for selecting preferred seats
3. **Passenger Information**: Form for entering passenger details with validation
4. **Payment Processing**: Multiple payment options (Credit Card, PayPal, Apple Pay)
5. **Booking Confirmation**: Confirmation page with booking details
6. **E-Ticket**: Downloadable and printable boarding pass

### Additional Features
- **Responsive Design**: Works on all devices (desktop, tablet, mobile)
- **Form Validation**: Client-side and server-side validation for all forms
- **Modern UI Components**: Progress indicators, animations, and interactive elements

## Technical Details

### Technologies Used
- **Frontend**: HTML5, CSS3, JavaScript, jQuery, Bootstrap 5
- **Backend**: PHP
- **Database**: MySQL
- **Icons**: Font Awesome
- **Fonts**: Google Fonts (Poppins)


### Database Schema
The system uses the following main tables:
- `users`: User account information
- `flights`: Flight details including routes, times, and prices
- `bookings`: Booking information linking users and flights
- `seats`: Seat information and availability

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache web server
- XAMPP/WAMP/MAMP for local development

### Setup Instructions
1. Clone the repository to your web server directory (e.g., `htdocs` for XAMPP)
2. Import the database schema from `db.sql`
3. Configure the database connection in `includes/db.php`
4. Ensure the `uploads` directory has write permissions
5. Access the application through your web browser (e.g., `http://localhost/AIRELINES`)

## Usage Guide

### For Users
1. Register for an account or log in
2. Search for flights by entering origin, destination, and travel dates
3. Select a flight from the search results
4. Choose a seat from the interactive seat map
5. Enter passenger details and proceed to payment
6. Complete payment using one of the available methods
7. Receive booking confirmation and download your e-ticket
8. View and manage bookings through the user dashboard

### For Administrators
Admin functionality is currently under development and will include:
- Flight management
- User management
- Booking management
- System settings

## Future Enhancements
- Admin dashboard for managing flights and bookings
- Multi-city and round-trip booking options
- Loyalty program integration
- Flight status updates and notifications
- Mobile app development
- Integration with third-party payment gateways
- Advanced reporting and analytics

