<% if VimeosInThisSection %>
<div class="vimeosInThisSection typography">
	<h4>Also see ... </h4>
<ul>
<% control VimeosInThisSection %>
<% if VimeoDataObject %>
<li>
	<a href="$Link" title="$VimeoDataObject.Title.ATT">
		<% control VimeoDataObject %>
		<img src="$IconLink.URL" alt="$Title.ATT" height="50" />
		<% end_control %>
	</a>
</li>
<% end_if %>
<% end_control %>
</ul>
<div class="clear"></div>
</div>
<% end_if %>
