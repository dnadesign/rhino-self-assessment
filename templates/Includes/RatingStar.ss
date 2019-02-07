<% if $Rating >= $Index %>
    <%-- Fill --%> 
    $SVG('rating-star-fill').extraClass('self-assessment-rating-star self-assessment-rating-star--fill').size(16, 16)
    <% else %>
    
    <%-- No fill --%> 
    $SVG('rating-star-fill').extraClass('self-assessment-rating-star self-assessment-rating-star--no-fill').size(16, 16)
<% end_if %>