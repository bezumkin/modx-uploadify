<li class="span3">
	<div class="thumbnail">
	  <img src="/[[+thumb:default=`[[+image]]`]]" width="300" />
  		<div class="caption">
    		<span>Прямая ссылка на файл</span>
    		<input type="text" value="http://st.bezumkin.ru/[[+image]]" onclick="this.select();" />
    		<span>Картинка с тегом</span>
    		<textarea onclick="this.select();"><img src="http://st.bezumkin.ru/[[+image]]" /></textarea>
    		[[+thumb:notempty=`
    		<span>Увеличение по клику</span>
    		<textarea onclick="this.select();"><a rel="fancybox" href="http://st.bezumkin.ru/[[+image]]"><img src="http://st.bezumkin.ru/[[+thumb]]" width="300" class="fancybox thumbnail center"></a></textarea>
    		`]]
  		</div>
	</div>
</li>