$(document).ready(function() {
    // Set default dates
    const today = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);
    
    // Format date as YYYY-MM-DD
    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }
    
    // Set default values for date inputs
    $('#departDate, #departDateOne').val(formatDate(today));
    $('#returnDate').val(formatDate(tomorrow));
    
    // Ensure return date is after departure date
    $('#departDate').change(function() {
        const departDate = new Date($(this).val());
        const returnDate = new Date($('#returnDate').val());
        
        if (returnDate <= departDate) {
            const newReturnDate = new Date(departDate);
            newReturnDate.setDate(departDate.getDate() + 1);
            $('#returnDate').val(formatDate(newReturnDate));
        }
    });
    
    // Form validation - handled by inline validation in index.php
    // Removing duplicate validation here to prevent conflicts
    
    // Copy values between forms when switching tabs
    $('#flightTab button').click(function() {
        if ($(this).attr('id') === 'roundtrip-tab') {
            $('#fromCityOne').val($('#fromCity').val());
            $('#toCityOne').val($('#toCity').val());
            $('#departDateOne').val($('#departDate').val());
            $('#passengersOne').val($('#passengers').val());
            $('#classOne').val($('#class').val());
        } else {
            $('#fromCity').val($('#fromCityOne').val());
            $('#toCity').val($('#toCityOne').val());
            $('#departDate').val($('#departDateOne').val());
            $('#passengers').val($('#passengersOne').val());
            $('#class').val($('#classOne').val());
        }
    });
    
    // Smooth scroll for anchor links
    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        
        $('html, body').animate({
            scrollTop: $($(this).attr('href')).offset().top - 100
        }, 500, 'linear');
    });
    
    // Animate elements when they come into view
    function animateOnScroll() {
        $('.feature-box, .destination-card').each(function() {
            const elementTop = $(this).offset().top;
            const elementVisible = 150;
            const windowHeight = $(window).height();
            const scrollY = $(window).scrollTop();
            
            if (elementTop < (scrollY + windowHeight - elementVisible)) {
                $(this).addClass('animated');
            }
        });
    }
    
    // Run animation check on scroll
    $(window).on('scroll', animateOnScroll);
    
    // Run once on page load
    animateOnScroll();
    
    // City autocomplete (simulated)
    const popularCities = [
        "New York", "London", "Tokyo", "Paris", "Dubai",
        "Los Angeles", "Chicago", "Toronto", "Sydney", "Singapore",
        "Hong Kong", "Berlin", "Rome", "Madrid", "Amsterdam"
    ];
    
    $('[id^=fromCity], [id^=toCity]').on('input', function() {
        const input = $(this).val().toLowerCase();
        const datalist = $(this).attr('id') + 'List';
        
        // Clear existing options
        $('#' + datalist).empty();
        
        if (input.length > 1) {
            // Filter cities that match input
            const matches = popularCities.filter(city => 
                city.toLowerCase().includes(input)
            );
            
            // Add matching cities to datalist
            matches.forEach(city => {
                $('#' + datalist).append(`<option value="${city}">`);
            });
        }
    });
});
