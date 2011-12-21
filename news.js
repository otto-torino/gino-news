function changeShowcaseNews(id) {

	var item_on = $$('div[id^=showcase_items_news_]')[0].getChildren('.active')[0];
	
	var myfx = new Fx.Tween(item_on, {'property': 'opacity'});

	item_on_zindex = item_on.getStyle('z-index');
	item_on.setStyle('z-index', item_on_zindex.toInt() + 1);

	$('news_'+id).setStyle('z-index', item_on_zindex);
	
	myfx.start(1,0).chain(function() { 
		item_on.setStyle('z-index', item_on_zindex.toInt() - 1);
		myfx.set(1);
	});

	item_on.removeClass('active');
	$('sym_' + item_on.id.substr(5)).removeClass('on');
	$('sym_' + id).addClass('on');

	$('news_'+id).addClass('active');
	

}
