$(".duty_description").each(function () {
   var description = $(this).text().substring(0,400);
   $(this).text(description+"...");
});

$("#conversation_content").animate({ scrollTop: $(this).height() }, "slow");

$('#table_top_givers').bootstrapTable("destroy").bootstrapTable({
   exportOptions: {
      fileName: 'BoiteASel_Top_5_Depanneurs'
   }
});

$('#table_top_askers').bootstrapTable("destroy").bootstrapTable({
   exportOptions: {
      fileName: 'BoiteASel_Top_5_Demandeurs'
   }
});

$('#table_actuality').bootstrapTable("destroy").bootstrapTable({
   exportOptions: {
      fileName: 'BoiteASel_Actualite'
   }
});

$('#table_activity_type').bootstrapTable("destroy").bootstrapTable({
   exportOptions: {
      fileName: 'BoiteASel_Types_Services'
   }
});

$('#table_exchanges').bootstrapTable("destroy").bootstrapTable({
   exportOptions: {
      fileName: 'BoiteASel_Volume_Echanges'
   }
});