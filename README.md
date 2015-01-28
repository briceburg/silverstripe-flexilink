silverstripe-flexilink
========================

Link to SiteTree Pages, External URLs, YouTube Videos, &c. from a single, flexible SilverStripe field.


Requirements
------------

[SilverStripe](http://www.silverstripe.org/) 3+


Screenshots
-----------

![flexichoice field](docs/screenshots/silverstripe-flexilink.gif?raw=true)

See [silverstripe-flexichoice](https://github.com/briceburg/silverstripe-flexichoice/) for a similar text input field.


Usage 
=====

* Add `FlexiLink` field types to your `DataObject`(s) 

```php
class BlockContentHeading extends DataObject {
  private static $db = array(
    'Title'     => 'Varchar',
    'Content'   => 'Text',
    'Link'      => 'FlexiLink',   // <--- here
    'LinkText'  => 'FlexiChoice', 
  );
  
```

Trigger the environment builder (/dev/build) after extending objects --
You will now see the `FlexiLinkField` appear in the CMS when editing your
object. 


* `FlexiLink` provides the following public template methods
  * **Type** : The type of link (e.g. 'ExternalURL','Page','YouTubeID')
  * **Value** : The raw value of the link 
  * **URL** : The link transformed into a URL (E.g. Page->Link(), 'http://www.google.com/', '//www.youtube.com/embed/[Value]')
  
```html
<div class="block-heading">
  <h1>$Title</h1>
  $Content
  
  <% if Link.exists %>
    <div class="link">
  
    <% if Link.Type == 'YouTubeID' %>
      <a class="button" href="$Link.URL" target="_blank">WATCH <span>MOVIE</span></a>
    <% else %>
      <a class="button" href="$Link.URL">$LinkText</a>
    <% end_if %>
    
    </div>
  <% end_if %>
  
</div>

```

* You may define link selection types and their related fields in [YAML Configuration](http://doc.silverstripe.org/framework/en/topics/configuration).
Here's an example **/mysite/config/_config.yml**

```yaml
---
Name: mysite
After:
  - 'framework/*'
  - 'cms/*'
---
# YAML configuration for SilverStripe
# See http://doc.silverstripe.org/framework/en/topics/configuration
# Caution: Indentation through two spaces, not tabs
SSViewer:
  theme: 'site'
  
FlexiLink:
  allowed_types:
    - Page
    - ExternalURL
    - YouTubeID
    - Google
    
FlexiLinkField:
  field_types:
    Page:
      field: TreeDropdownField
      description: Select a Page to Link To
    ExternalURL: 
      field: TextField
      description: URL to link to (include http:// ...)
    YouTubeID:
      field: TextField
      description: YouTube Video ID
    Google:
      field: TextField
      description: TestTest
```

Whenever making YML configuration changes, be sure to ?flush=all to register 
them in the manifest.


