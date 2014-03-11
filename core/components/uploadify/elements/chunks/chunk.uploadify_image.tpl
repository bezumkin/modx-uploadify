<div class="thumbnail span3 col-md-3">
	<a href="[[+image]]" target="_blank">
		<img src="[[+thumb:default=`[[+image]]`]]" />
	</a>
	<div class="caption">
		<span>[[%uf_frontend_image_direct]]</span>
		<input type="text" value="[[+image]]" onclick="this.select();" class="form-control" />

		<span>[[%uf_frontend_image_tag]]</span>
		<textarea onclick="this.select();" class="form-control" rows="3"><img src="[[+image]]" /></textarea>

	[[+thumb:notempty=`
		<span>[[%uf_frontend_image_zoom]]</span>
		<textarea onclick="this.select();" class="form-control" rows="3"><a rel="fancybox" href="[[+image]]"><img src="[[+thumb]]" class="fancybox thumbnail center"></a></textarea>
	`]]
	</div>
</div>