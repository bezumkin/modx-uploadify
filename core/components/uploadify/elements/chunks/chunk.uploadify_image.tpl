<li class="span3">
	<div class="thumbnail">
	  <img src="[[+thumb:default=`[[+image]]`]]" />
		<div class="caption">
			<span>[[%uf_frontend_image_direct]]</span>
			<input type="text" value="[[+image]]" onclick="this.select();" />
			<span>[[%uf_frontend_image_tag]]</span>
			<textarea onclick="this.select();"><img src="[[+image]]" /></textarea>
		[[+thumb:notempty=`
			<span>[[%uf_frontend_image_zoom]]</span>
			<textarea onclick="this.select();"><a rel="fancybox" href="[[+image]]"><img src="[[+thumb]]" class="fancybox thumbnail center"></a></textarea>
		`]]
		</div>
	</div>
</li>