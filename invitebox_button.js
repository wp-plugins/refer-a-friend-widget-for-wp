(function() {

var widget_script = "<script id='invitebox-script' type='text/javascript'>" +
"(function() {" +
"    var ib = document.createElement('script');" +
"    ib.type = 'text/javascript';" +
"    ib.async = true;" +
"    ib.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'invitebox.com/invitation-camp/" + ib_url +  "/invitebox.js?key=" + ib_key + "&jquery='+(typeof(jQuery)=='undefined');" +
"    var s = document.getElementsByTagName('script')[0];" +
"    s.parentNode.insertBefore(ib, s);" +
"})();" +
"</script><a id='invitebox-href' href='https://invitebox.com/widget/"+ ib_url +"/share'>[referral program]</a>";

var tracking_script = "<div id='wp_ib_tracking'>[Tracking Script]</div><script id='invitebox-track' type='text/javascript'>" +
"(function() {" +
"    var ibl = document.createElement('script');" +
"    ibl.type = 'text/javascript'; ibl.async = true;" +
"    ibl.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'invitebox.com/invitation-camp/"+ ib_url +"/invitebox-landing.js?hash='+escape(window.location.hash);" +
"    var s = document.getElementsByTagName('script')[0];" +
"    s.parentNode.insertBefore(ibl, s);" +
"document.getElementById('wp_ib_tracking').style.display='none';})();</script>";

tinymce.PluginManager.add('invitebox_button', function( editor, url ) {
    editor.addButton( 'invitebox_button', {
        title: 'InviteBox',
        icon: false,
	image: url + '/logo_200_1.png',
        type: 'menubutton',
	classes: 'widget btn wp_ib_button',
        menu: [
        {text: 'Add Widget Script', onclick: function() {
console.log(this);
            if(tinymce.activeEditor.dom.get('invitebox-href') == null)
                tinymce.activeEditor.getBody().appendChild(tinymce.activeEditor.dom.create('div', {}, widget_script));
        }},
        {text: 'Add Tracking Script', onclick: function() {
            if(tinymce.activeEditor.dom.get('invitebox-track') == null)
                tinymce.activeEditor.getBody().appendChild(tinymce.activeEditor.dom.create('div', {}, tracking_script));
        }},
        {text: 'Remove Widget Script', onclick: function() {
            var element = tinymce.activeEditor.dom.get('invitebox-script');
            if (element)
                element.parentElement.remove();
        }},
        {text: 'Remove Tracking Script', onclick: function() {
            var element = tinymce.activeEditor.dom.get('invitebox-track');
            if (element)
                element.remove();
            var element_title = tinymce.activeEditor.dom.get('wp_ib_tracking');
            if(element_title)
                element_title.parentElement.remove();
        }}]
    });
});
})();
