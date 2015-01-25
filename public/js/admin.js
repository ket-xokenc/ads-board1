/**
 * Created by ed on 22.01.15.
 */
$(document).ready(function () {
    $(".delbutton").on('click', function () {
        var elem = $(this);
        var del_id = elem.data("del");
        var info = 'delete=' + del_id;
        if (confirm("Вы уверены что хотите удалить?")) {
            $.ajax({
                type: "POST",
                url: "add",
                data: info,
                success: function () {
                }
            });
            $(this).parents(".record").animate({backgroundColor: "#fbc7c7"}, "fast")
                .animate({opacity: "hide"}, "slow");
        }
        return false;
    });

    $(".my_form_adm").submit(function () {
        return false;
    });
    $(".sch-act").click(function () {
        var search = $(".input-sch").val();
        $.post("admin/search",
            {
                search: search
            }, function (data) {
                $('#result').html(data);
            });
    });

    var max_fields = 10;
    var wrapper = $(".input_fields_wrap");
    var add_button = $(".add_field_button");
    var x = 1;
    $(add_button).click(function (e) {
        e.preventDefault();
        if (x < max_fields) {
            x++;
            $(wrapper).append('<div><input type="text" class="form-control" placeholder="Value" name="property_values[]"/><a href="#" class="remove_field">Remove</a></div>');
        }
    });

    $(wrapper).on("click", ".remove_field", function (e) {
        e.preventDefault();
        $(this).parent('div').remove();
        x--;
    });


    $(".update_prop").on("click", function(){
        var elem = $(this).data("update");
        console.log(elem);
    });

});