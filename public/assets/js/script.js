jQuery(".duty_description").each(function () {
   var description = $(".duty_description").text().substring(0,300);
   $(".duty_description").text(description+"...");
});

$("#conversation_content").animate({ scrollTop: $(this).height() }, "slow");
