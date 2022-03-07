require(['core/first', 'jquery', 'jqueryui', 'core/ajax'], function(core, $, bootstrap, ajax) {

  //Using Jquery, when the page loads, this function will load
  $(document).ready(function() {
  
    //Params finds all of the key values and append it to  
    var params = {};
      window.location.search
      .replace(/[?&]+([^&]+)=([^&]*)/gi, function(str, key, value) {
        params[key] = value;
      });
      if (params['month']){
        $('#month option[value=' + params['month']+ ']').attr('selected', 'selected');
      }
      if (params['year']){
        $('#year option[value=' + params['year']+ ']').attr('selected', 'selected');
      }
    $('#search').click(function(){
      searchcourses();
    });
    
    function searchcourses() {
      console.log('search courses')
      window.open("/../local/coursecatalogue/index.php?month=" + $('#month').val() + "&year=" + $('#year').val(), '_self')
      
    }
  });
});