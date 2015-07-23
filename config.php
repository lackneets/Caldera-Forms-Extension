<div class="caldera-config-group">
  <label><?php echo __('Post Type Slug'); ?> </label>
  <div class="caldera-config-field">
    <input type="text" class="block-input field-config caldera-field-bind required" id="{{_id}}_slug" name="{{_name}}[slug]" value="{{slug}}" required="required">
  </div>
</div> 

<div class="caldera-config-group">
  <label><?php echo __('Slug Migration'); ?> </label>
  <div class="caldera-config-field">
    <select class="block-input field-config" name="{{_name}}[slug_migrate]">
      <option value="migrate"{{#is privacy value="migrate"}} selected="selected"{{/is}}>Migrate if changed</option>
      <option value="keep"{{#is privacy value="keep"}} selected="selected"{{/is}}>Keep as is</option>
    </select>
  </div>
</div>

<div class="caldera-config-group">
  <label><?php echo __('Post Type Privacy'); ?> </label>
  <div class="caldera-config-field">
    <select class="block-input field-config" name="{{_name}}[privacy]">
      <option value="public"{{#is privacy value="public"}} selected="selected"{{/is}}>Public</option>
      <option value="private"{{#is privacy value="private"}} selected="selected"{{/is}}>Private</option>
    </select>
  </div>
</div>
<!-- <p class="description"></p> -->

<div class="caldera-config-group">
  <label><?php echo __('Title Field'); ?> </label>
  <div class="caldera-config-field">
    <input type="text" class="block-input field-config magic-tag-enabled caldera-field-bind required" id="{{_id}}_title_field" name="{{_name}}[title_field]" value="{{title_field}}">
  </div>
</div> 

<input type="hidden" value="{{slug}}" name="{{_name}}[previous_slug]">
<input type="hidden" value="{{_id}}" name="config[cfx_submission_id]">