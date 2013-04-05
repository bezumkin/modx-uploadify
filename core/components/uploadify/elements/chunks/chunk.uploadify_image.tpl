<li class="span3">
	<div class="thumbnail">
	  <img src="/[[+thumb:default=`[[+image]]`]]" width="300" />
		<div class="caption">
			<span>[[%uf_frontend_image_direct]]</span>
			<input type="text" value="[[++site_url]][[+image]]" onclick="this.select();" />
			<span>[[%uf_frontend_image_tag]]</span>
			<textarea onclick="this.select();"><img src="[[++site_url]][[+image]]" /></textarea>
			[[+thumb:notempty=`
			<span>[[%uf_frontend_image_zoom]]</span>
			<textarea onclick="this.select();"><a rel="fancybox" href="[[++site_url]][[+image]]"><img src="[[++site_url]][[+thumb]]" width="300" class="fancybox thumbnail center"></a></textarea>
			`]]
		</div>
	</div>
</li>