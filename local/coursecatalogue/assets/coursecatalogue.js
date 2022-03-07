//These requires are set for our use
require(['core/first', 'jquery', 'jqueryui', 'core/ajax'], function(core, $, bootstrap, ajax) {

            $(document).ready(function() {

                        $('#search').click(function() {
                            searchusers();
                        });

                        //Using Jquery, when the page loads, this function will load
                        $(document).ready(function() {

                            //Params finds all of the key values and append it to  
                            var params = {};
                            window.location.search
                                .replace(/[?&]+([^&]+)=([^&]*)/gi, function(str, key, value) {
                                    params[key] = value;
                                });
                            if (params['month']) {
                                $('#month option[value=' + params['month'] + ']').attr('selected', 'selected');
                            }
                            if (params['year']) {
                                $('#year option[value=' + params['year'] + ']').attr('selected', 'selected');
                            }
                            $('#search').click(function() {
                                searchcourses();
                            });
                        });