jQuery(document).ready(function ($) {
  /**
   * Remove Single Image.
   * */
  jQuery(document).on("click", ".remove-image", function () {
    easy_pg_remove_img(this);
  });

  if (jQuery(".gallery_single_row").length <= 1) {
    jQuery(".remove_all_media").hide();
  }
  /**
   * Remove All Images
   * */
  jQuery(document).on("click", ".remove_all_media", function () {
    jQuery("#img_box_container").html("");
    jQuery(".remove_all_media").hide();
  });

  var media_uploader = null;

  // Change Image
  jQuery(document).on("click", ".change-image", function () {
    easy_pg_open_media_uploader_image_this(this);
  });

  // Add New Image
  jQuery(document).on("click", ".add-new-image", function () {
    if (jQuery(".gallery_single_row").length > 0) {
      jQuery(".remove_all_media").show();
    }
    easy_pg_open_media_uploader_image_plus();
  });

  // Function to remove image
  function easy_pg_remove_img(value) {
    var thisval = jQuery(value);
    var parent = thisval.parent().parent();
    parent.remove();
  }

  // Function to open media uploader for a single image
  function easy_pg_open_media_uploader_image_this(value) {
    media_uploader = wp.media({
      frame: "post",
      state: "insert",
      multiple: false,
    });
    var thisval = jQuery(value);

    media_uploader.on("insert", function () {
      var json = media_uploader.state().get("selection").first().toJSON();
      var image_url = json.url;
      console.log(image_url);
      thisval.attr("src", image_url);
      thisval.siblings(".meta_image_url").val(image_url);
    });
    media_uploader.open();
  }

  // Function to open media uploader for multiple images
  function easy_pg_open_media_uploader_image_plus() {
    var media_uploader = wp.media({
      frame: "select",
      title: "Select Images",
      button: {
        text: "Add Images",
      },
      multiple: true, // Enable multiple selections
    });

    media_uploader.on("open", function () {
      // This will allow multi-select without holding CTRL
      media_uploader.content.get().collection.props.set({ multiple: true });
    });

    media_uploader.on("select", function () {
      var selection = media_uploader.state().get("selection");
      var img_container = jQuery("#img_box_container");

      selection.map(function (image) {
        var image_url = image.toJSON().url;
        var box = jQuery("#master_box").html();
        jQuery(box).appendTo(img_container);
        var element = img_container
          .find(".gallery_single_row:last-child")
          .find(".image_container");
        var html =
          '<img class="gallery_img_img change-image" src="' +
          image_url +
          '" height="55" width="55"/>';
        element.append(html);
        element.find(".meta_image_url").val(image_url);
        console.log(image_url);
      });
    });

    media_uploader.open();
  }

  // Initialize sortable for image container
  jQuery(function () {
    jQuery("#img_box_container").sortable();
  });
});
