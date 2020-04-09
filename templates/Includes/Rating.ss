<div class="rating">
    <div class="rating--visual">        
        <i class="star<% if $Rating >= 1 %> star--full<% end_if %>"></i>
        <i class="star<% if $Rating >= 2 %> star--full<% end_if %>"></i>
        <i class="star<% if $Rating >= 3 %> star--full<% end_if %>"></i>
        <i class="star<% if $Rating >= 4 %> star--full<% end_if %>"></i>
        <i class="star<% if $Rating >= 5 %> star--full<% end_if %>"></i>
    </div>
    <span class="rating--textual">Rating is $Rating <% if $Rating == 1 %>star<% else %>stars<% end_if %></span>
</div>
