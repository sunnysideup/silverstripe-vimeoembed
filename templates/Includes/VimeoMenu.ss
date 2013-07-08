<% if VimeosInThisSection.count %>
<div class="vimeosInThisSection typography">
	<h4>Also see ... </h4>
<ul>
<% loop VimeosInThisSection %>
<% if VimeoDataObject %>
<li>
	<a href="$Link" title="$VimeoDataObject.Title.ATT">
		<% with VimeoDataObject %>
		<img src="$IconLink.URL" alt="$Title.ATT" height="50" />
		<% end_with %>
	</a>
</li>
<% end_if %>
<% end_loop %>
</ul>
<div class="clear"></div>
</div>
<% end_if %>
