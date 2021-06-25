function initRepairGet() {
  var o = {
    get: function() {
      return "id=" + $E("id").value + "&" + this.name + "=" + this.value;
    },
    onSuccess: function() {
      topic.valid();
      product_no.valid();
    },
    onChanged: function() {
      topic.reset();
      product_no.reset();
    }
  };
  var topic = initAutoComplete(
    "topic",
    WEB_URL + "index.php/repair/model/autocomplete/find",
    "topic,product_no",
    "find",
    o
  );
  var product_no = initAutoComplete(
    "product_no",
    WEB_URL + "index.php/repair/model/autocomplete/find",
    "product_no,topic",
    "find",
    o
  );
}