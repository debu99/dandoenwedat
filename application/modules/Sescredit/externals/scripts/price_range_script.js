jqueryObjectOfSes(document).ready(function(){
  jqueryObjectOfSes('#price-range-submit').hide();
  jqueryObjectOfSes("#min_price,#max_price").on('change', function () {
    jqueryObjectOfSes('#price-range-submit').show();
    var min_price_range = parseInt(jqueryObjectOfSes("#min_price").val());
    var max_price_range = parseInt(jqueryObjectOfSes("#max_price").val());
    if (min_price_range > max_price_range) {
      jqueryObjectOfSes('#max_price').val(min_price_range);
    }
    jqueryObjectOfSes("#slider-range").slider({
      values: [min_price_range, max_price_range]
    });
  });
  jqueryObjectOfSes("#min_price,#max_price").on("blur", function () {   
    var min_price_range = parseInt(jqueryObjectOfSes("#min_price").val());
    var max_price_range = parseInt(jqueryObjectOfSes("#max_price").val());
    if(min_price_range == max_price_range){
      max_price_range = min_price_range + 100;
      jqueryObjectOfSes("#min_price").val(min_price_range);		
      jqueryObjectOfSes("#max_price").val(max_price_range);
    }
    jqueryObjectOfSes("#slider-range").slider({
      values: [min_price_range, max_price_range]
    });
  });
  jqueryObjectOfSes("#min_price,#max_price").on("paste keyup", function () {                                        
    jqueryObjectOfSes('#price-range-submit').show();
    var min_price_range = parseInt(jqueryObjectOfSes("#min_price").val());
    var max_price_range = parseInt(jqueryObjectOfSes("#max_price").val());
    jqueryObjectOfSes("#slider-range").slider({
      values: [min_price_range, max_price_range]
    });
  });
  jqueryObjectOfSes(function () {
    jqueryObjectOfSes("#slider-range").slider({
      range: true,
      orientation: "horizontal",
      min: 0,
      max: 10000,
      values: [0, 10000],
      step: 1,
      slide: function (event, ui) {
        if (ui.values[0] == ui.values[1]) {
            return false;
        }
        jqueryObjectOfSes("#min_price").val(ui.values[0]);
        jqueryObjectOfSes("#max_price").val(ui.values[1]);
      }
    });
    var min =jqueryObjectOfSes("#min_price").val();
    var max = jqueryObjectOfSes("#max_price").val();
    jqueryObjectOfSes("#slider-range").slider({
      values: [min, max]
    });
  });
  jqueryObjectOfSes("#slider-range,#price-range-submit").click(function () {
    var min_price = jqueryObjectOfSes('#min_price').val();
    var max_price = jqueryObjectOfSes('#max_price').val();
    jqueryObjectOfSes("#searchResults").text("Here List of products will be shown which are cost between " + min_price  +" "+ "and" + " "+ max_price + ".");
  });
});