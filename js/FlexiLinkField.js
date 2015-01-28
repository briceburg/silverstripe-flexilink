(function($){
  
  var showCompositeLinkField = function($flexi, $type) {
    if($type.val()) {
      $('.FlexiLinkField' + $type.val() + ' .FlexiLinkCompositeField', $flexi).show();  
    } 
  };
  
  $('div.FlexiLinkField').entwine({
    onadd: function(){
      var $flexi = this,
          $type = $flexi.find('.FlexiLinkFieldType select');
      
      $type.on('change', function(){
        $('.FlexiLinkCompositeField',$flexi).hide();
        showCompositeLinkField($flexi, $type);
      });
      
      // show initial field
      showCompositeLinkField($flexi, $type);

      // Fix Tree URL
      var $tree = this.find('.TreeDropdownField');
      if ($tree.data('urlTree')) {
        $tree.data('urlTree', $tree.data('urlTree').replace(/\[(\w+)\](\/tree)/,'$2?type=$1'));
      }
    }
  });
  
  
})(jQuery);