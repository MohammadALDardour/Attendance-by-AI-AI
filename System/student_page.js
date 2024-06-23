// Function to fetch courses from the server
function fetchCourses() {
    // Make an AJAX request to fetch courses from the server
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                // Parse the response JSON
                var courses = JSON.parse(xhr.responseText);
                // Call addCourses function with the fetched data
                addCourses(courses);
            } else {
                console.error('Failed to fetch courses: ' + xhr.status);
            }
        }
    };
    xhr.open('GET', 'fetch_courses.php', true);
    xhr.send();
}

// Function to add courses dynamically
function addCourses(courses) {
    var courseList = document.getElementById('course-list');
    courses.forEach(function(course) {
        var courseBtn = document.createElement('a');
        courseBtn.href = 'course_page.php?course_id=' + course.course_number + '&section_number=' + course.section_number;
        courseBtn.className = 'course-btn';
        courseBtn.innerHTML = '<span>' + course.course_symbol + '</span><br><span>' + course.course_number + '</span>';
        courseList.appendChild(courseBtn);
    });
}

// Call fetchCourses function to get courses from the server
fetchCourses();
