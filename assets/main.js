jQuery(document).on("click", "#" + make_magic.prefix + "_submit", function (e) {
  e.preventDefault();
  let ajax_data = {
    "username": jQuery( this ).parent().find("input[type='text']").val()
  };

  jQuery.ajax({
    url: make_magic.rest_root,
    method: "POST",
    dataType: "json",
    data: JSON.stringify(ajax_data),
    beforeSend: function (xhr) {
      xhr.setRequestHeader('X-WP-Nonce', make_magic.nonce);
    }
  }).success(function (data) {
    alert("Saved!");
  }).error(function (results, status, error) {
    alert("Error!");
  });
});

jQuery(document).on("click", "#" + make_magic.prefix + "_search", function (e) {
  e.preventDefault();
  let ajax_data = {
    "username": jQuery( this ).parent().find("input[type='text']").val()
  };

  jQuery.ajax({
    url: make_magic.rest_root,
    method: "GET",
    dataType: "json",
    data: ajax_data,
    beforeSend: function (xhr) {
      xhr.setRequestHeader('X-WP-Nonce', make_magic.nonce);
    }
  }).success(function (data) {
    jQuery("#" + make_magic.prefix + "_items").empty();
    jQuery.each(data, function( index, value ) {
      console.log( index + ": " + value );
      jQuery("#" + make_magic.prefix + "_items").append(jQuery('<div>').html(value));
    });
  }).error(function (results, status, error) {
    alert("Error!");
  });
});