//These requires are set for our use
require(['core/first', 'jquery','jqueryui', 'core/ajax'], function(core, $, bootstrap, ajax) {
   
    $(document).ready(function(){
       
        var params = {};
        window.location.search
        .replace(/[?&]+([^=&]+)=([^&]*)/gi, function(str, key, value) {params[key] = value;
       });
       
       if (params['month']) {
        $('#month option[value=' +params['month']+ ']').attr('selected', 'selected');
        }

        if (params['year']) {
            $('#year option[value=' +params['year']+ ']').attr('selected', 'selected');
            }
        
         if (params['tags']) {
            $('#tags option[value=' +params['tags']+ ']').attr('selected', 'selected');
                }

          if (params['instructor']) {
            $('#instructor option[value=' +params['instructor']+ ']').attr('selected', 'selected');
                    }
            if (params['ratings']) {
                        $('#ratings option[value=' +params['ratings']+ ']').attr('selected', 'selected');
                                }

        $('#search').click(function(){
            searchcourses();
        });

        function searchcourses() {
            console.log('search users');
            window.open("/lms-moodle/local/coursecatalogue/index.php?month=" + $('#month').val() + "&year=" + $('#year').val() + 
            "&tags=" + $('#tags').val() + "&instructor=" + $('#instructor').val() + "&ratings=" + $('#ratings').val(), '_self');
        }
    });
});