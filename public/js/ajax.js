/**
 * Created by alexandr on 11.01.15.
 */

$(function() {
    $('.myform2').submit(function(){return false});
    $("#search_box").keyup(function() {
        // получаем то, что написал пользователь
        var searchString    = $("#search_box").val();
        // формируем строку запроса
        var data            = 'search='+ searchString;

        // если длина searchString > 3
        if(searchString.length >= 1) {
            // делаем ajax запрос
            $.ajax({
                type: "POST",
                url: "search",
                data: data,
                beforeSend: function(html) { // запустится до вызова запроса
                    $("#results").html('');
                },
                success: function(html){ // запустится после получения результатов
                    $("#results").show();
                    $("#results").append(html);
                }
            });
        }
        return false;
    });
});