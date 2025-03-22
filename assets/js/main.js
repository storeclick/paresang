$(document).ready(function() {
    // Sidebar toggle
    $('.sidebar-toggle').on('click', function() {
        $('.sidebar').toggleClass('active');
        $('.main-content').toggleClass('active');
    });

    // Dropdown menu
    $('.dropdown-toggle').on('click', function(e) {
        e.preventDefault();
        $(this).next('.collapse').collapse('toggle');
    });

    // Active link highlighting
    const currentLocation = window.location.pathname;
    $('.nav-link').each(function() {
        const link = $(this).attr('href');
        if (currentLocation.includes(link)) {
            $(this).addClass('active');
            $(this).closest('.collapse').addClass('show');
        }
    });
});