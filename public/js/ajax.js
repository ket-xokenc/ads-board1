/**
 * Created by alexandr on 11.01.15.
 */

$(function() {
    $('.myform2').submit(function(){return false});
    $("#search_boxx").click(function() {
        // получаем то, что написал пользователь
        var searchString    = $("#search_box").val();
        // формируем строку запроса
        var data            = 'search='+ searchString;

        // если длина searchString > 3
        if(searchString.length >= 3) {
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

    var working = false;

    $('#addCommentContainer').on('submit', '#addCommentFormEnd', function (e) {
        e.preventDefault();
        if (working) return false;
        var a;
        a = this;

        working = true;
        $('#submit').val('send');
        $('span.error').remove();

        $.post('add-comment', $(this).serialize(), function (msg) {
            working = false;
            if (msg.status) {
                $('#addNewComment').before(msg.html);
                commentForm.remove();
                var res = $(this).find('input').eq(1).attr('pid');
                if(res ==  '0') {
                    $('#addCommentContainer').siblings(":last").after($('addNewComment').content());
                }
            }
            else {

                $.each(msg.errors, function (k, v) {
                    $('textarea#body').before(' <span style="color:red" class="error">' + v + '</span>');
                });
            }
        }, 'json');

    });

    $('#addCommentContainer').on('submit', '#addCommentForm', function (e) {
        e.preventDefault();
        if (working) return false;
        var a;
        a = this;

        working = true;
        $('#submit').val('send');
        $('span.error').remove();

        $.post('add-comment', $(this).serialize(), function (msg) {
            working = false;
            if (msg.status) {
                $('#addCommentContainer').siblings(":last").after(msg.html);
                commentForm.remove();
                var res = $(this).find('input').eq(1).attr('pid');
                if(res ==  '0') {
                    $('#addCommentContainer').siblings(":last").after($('addNewComment').content());
                }
            }
            else {

                $.each(msg.errors, function (k, v) {
                    $('label[for=' + k + ']').append(' <span style="color:red" class="error">' + v + '</span>');
                });
            }
        }, 'json');

    });
    var commentForm;
    $('#addNewComment, .reply').on('click', function (e) {
        if (commentForm) {
            commentForm.remove();
        }
        var current = $(this);

        commentForm = $('#addCommentContainer').clone(true, true);
        if ($(this).attr('id') == 'addNewComment') {
            commentForm.find('form').attr('id', 'addCommentFormEnd');
            commentForm.appendTo(current.parent());
        } else {
            commentForm.insertAfter(current.parent());
            commentForm.css('marginTop', 5);
            commentForm.children('p').hide();
            var pid;
            pid = current.parent().find('#id').val();
            commentForm.find('#pid').attr('value', pid);
        }
        commentForm.show();

        return false;
    });

});