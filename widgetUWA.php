<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:widget="http://www.netvibes.com/ns/">
<head>

<title>SemanticScuttle</title>

<meta name="author" content="Benjamin HKB (inspired by Florent Solt)" />
<meta name="description" content="" />
<meta name="keywords" content="semanticscuttle delicious del.icio.us" />

<meta name="apiVersion" content="1.0" />
<meta name="inline" content="true" />
<meta name="autoRefresh" content="20" />
<meta name="debugMode" content="false" />

<link rel="icon" href="http://semanticscuttle.sourceforge.net/icon.png"/>

<link rel="stylesheet" type="text/css" href="http://www.netvibes.com/themes/uwa/style.css" />
<script type="text/javascript" src="http://www.netvibes.com/js/UWA/load.js.php?env=Standalone"></script>

<widget:preferences>
    <preference name="website" label="URL of the SemanticScuttle website" type="text" defaultValue="http://example.com" />
    <preference name="websiteTitle" label="Website's title" type="text" defaultValue="Website Name" />
    <preference name="account" label="User" type="text" defaultValue="" />
    <preference name="tags" label="Tags (optionally separated by +)" type="text" defaultValue="tag1+tag2" />
    <preference name="thumb" label="Show thumbnails" type="boolean" defaultValue="true" />
    <preference name="limit" type="range" label="Number of items to display" defaultValue="20" step="1" min="1" max="50" />
    <preference name="offset" type="hidden" defaultValue="0" />
</widget:preferences>

<style type="text/css">
.container ul {
    padding: 0px;
}
.container .clear {
    clear: both;
    height: 1px;
    font-size: 1px;
    line-height: 1px;
}
.container li {
    background-image: none;
    padding: 2px;
    clear: both;
    margin: 0px;
}
.container li a {
    font-weight: bold;
}
.container li em {
    display: block;
}
.container li img {
    display: block;
    float: left;
    margin-right: 4px;
    margin-bottom: 4px;
}
.container li img.thumb {
    width: 60px;
    height: 45px;
    border: 1px solid #ccc;
}
.container li.cal {
    padding-left: 20px;
    background-image: url(http://www.netvibes.com/img/icons/time.png);
    background-repeat: no-repeat;
    background-position: left 1em;
    line-height: 16px;
    margin-bottom: 0.5em;
    padding-top: 1em;
    font-size: 1.3em;
    border-bottom: 1px dotted #aaa;
}

</style>

<script type="text/javascript">
if (document.location && document.location.hostname == 'mymodules.local') {
  UWA.proxies.ajax = 'ajaxProxy.php';
}

widget.months = {
    1:_('January'), '2':_('February'), 3:_('March'), 4:_('April'), 5:_('May'), 6:_('June'),
    7:_('July'), 8:_('August'), 9:_('September'), 10:_('October'), 11:_('November'), 12:_('December')
}

widget.onLoad =  function() {
    widget.onRefresh();
}

widget.onRefresh = function() {
  widget.setBody( _("Loading ... (if loading is too long, check preferences: URL must be exact, user and tags must exist.)") );
  var website = widget.getValue('website');
  if(website.charAt(website.length-1) != '/') {
      website = website +'/';
  }
  
  var websiteTitle = widget.getValue('websiteTitle');
  
  var account = widget.getValue('account');
  if(account == undefined || account == '') {
      account = 'all';
  }
  var tags = widget.getValue('tags');
  if(tags != undefined && tags != '') {
      tags = '/' + tags;
      var reg=new RegExp(" ", "g");
      tags = tags.replace(reg, "%20");
  }
  if (website == undefined || website == 'http://example.com/') {
      widget.setBody(_("Edit the widget please."));
  } else {
      //widget.setBody(website + 'rss/' + account + tags);
      var title = '<a href="' + website + '">' + websiteTitle +'</a>';
      title+= ' / <a href="http://sourceforge.net/projects/semanticscuttle/">'+ 'SemanticScuttle</a>';
      widget.setTitle(title);
      UWA.Data.getFeed(website + 'rss/' + account + tags, widget.onData);
  }
}

widget.onData = function(data) {
    widget.items = data.items;
    //widget.log(data.items);
    widget.display();
}    

widget.display = function() {
    // Main container
    var container = widget.createElement('div');
    container.className = 'container';
    
    // Prev date
    var prev_date = null;
    
    // Items
    var ul = widget.createElement('ul');
    var limit = parseInt(widget.getValue('limit'));
    var offset = parseInt(widget.getValue('offset'));
    for (var i=offset; i<widget.items.length && i<limit+offset; i++) {
        
        var date = new Date(widget.items[i].date)

        // Cal
        if (prev_date == null ||
            date.getMonth() != prev_date.getMonth() ||
            date.getDate() != prev_date.getDate() ||
            date.getYear() != prev_date.getYear()) {
            prev_date = date;
            var cal = widget.createElement('li');
            cal.className = 'cal';

            var relative_to = new Date();
            var delta = parseInt((relative_to.getTime() - date.getTime()) / 1000);
            if (delta < 24*60*60) {
                cal.appendText(_('Today'));
            } else if (delta < 48*60*60) {
                cal.appendText(_('Yesterday'));
            } else {
                cal.appendText(date.getDate() + ' ' + widget.months[date.getMonth() + 1])
            }
            ul.appendChild(cal);
        }
        
        // Li
        var li = widget.createElement('li');
        if (i%2 == 1) li.className = 'even';
        
        if (widget.getValue('thumb') == 'true') {
            // Img
            var img = widget.createElement('img');
            img.src = "http://open.thumbshots.org/image.pxf?url=" + widget.items[i].link.replace(/^(http:\/\/[^\/]+).*$/, '$1');
            img.className = 'thumb';
            li.appendChild(img);
        } else {
            var img = widget.createElement('img');
            img.src = 'http://semanticscuttle.sourceforge.net/bookmark.gif'
            li.appendChild(img);
        }
        
        // Title
        var title = widget.createElement('a');
        title.setContent(widget.items[i].title);
        title.href = widget.items[i].link;
        if(widget.items[i].content == '') {
            widget.items[i].content = _("No description");
        }
        title.title= widget.items[i].content;
        li.appendChild(title);

        // // Tags
        // console.log(widget.items[i]);
        // var tagsText = widget.items[i].subject;
        // if (tagsText != null) {
        //     tags = widget.createElement('em');
        //     tags.appendText(tagsText.nodeValue);
        //     li.appendChild(tags);            
        // }
        
        // Clear
        var clear = widget.createElement('div');
        clear.className = 'clear';
        li.appendChild(clear);
        
        ul.appendChild(li);
    }
    container.appendChild(ul);

    // Pager
    var pager = widget.createElement('div');
    
    var pager_ctrl = new UWA.Controls.Pager( {
      module: this,
      limit: widget.getValue('limit'),
      offset: widget.getValue('offset'),
      dataArray: widget.items
    } );
    
    pager_ctrl.onChange = function(newOffset) {
      widget.setValue('offset', newOffset);
      widget.display();
    }
    pager.setContent(pager_ctrl.getContent());
    container.appendChild(pager);
    
    widget.setBody(container);
}

</script>

</head>
<body>
</body>
</html>
