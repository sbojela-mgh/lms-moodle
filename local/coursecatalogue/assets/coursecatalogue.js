//These requires are set for our use
require(['core/first', 'jquery','jqueryui', 'core/ajax'], function(core, $, bootstrap, ajax) {
   
    $(document).ready(function(){
       
        $('#search').click(function(){
            searchusers();
        });

        function searchusers() {
            console.log('search users');
            window.open("/local/coursecatalogue/index.php?month=" + $('#month').val() + "&year=" + $('#year').val(), '_self');
        }
    });
});