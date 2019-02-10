<div>
    <span class="sr-only">Rating is $Rating <% if $Rating == 1 %>star<% else %>stars<% end_if %></span>
    <% include RatingStar Rating=$Rating, Index=1 %>
    <% include RatingStar Rating=$Rating, Index=2 %>
    <% include RatingStar Rating=$Rating, Index=3 %>
    <% include RatingStar Rating=$Rating, Index=4 %>
    <% include RatingStar Rating=$Rating, Index=5 %>
</div>
