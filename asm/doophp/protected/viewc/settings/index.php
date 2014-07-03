<div class="content fl">

		<div class="contentbox">
			
			<input type="submit" value="Bestätigen" class="button-bl" />
			<input type="submit" value="Abbrechen" class="button-re" />

		</div>
		<div id="json"><?php echo $data['data']; ?></div>

		<script type="text/javascript">
		$(document).ready(function() {
			var str = $( "#json" ).text();
	
    
	    $('#example').dataTable( {
	    
        "sAjaxSource": "http://shelltec.de:1338/protected/controller/api.php/assetone/getUsers",
        "aaSorting": [[0, 'asc']],
        "sAjaxDataProp":"",
        "bAutoWidth": true,
        "bDeferRender": true,
        "bFilter": true,
        "bServerSide": false,
      
      "columns": [ {
				"mDataProp": "B_ID",
            	"title": "ID"
            }, {
            	"mDataProp": "B_Vorname",
            	"title": "Vorname" 
            }, {
            	"mDataProp": "B_Nachname",
            	"title": "Nachname"
            }, {
            	"mDataProp": "B_email",
				"title": "E-Mail"
			}, {
				"mDataProp": "Bg_ID",
				"title": "Bg_ID"
			}, {
				"mDataProp": "B_LastLogin",
				"title": "LastLogin"
			}, {
				"mDataProp": "Resethash",
				"title": "Resethash"
			}, {
				"mDataProp": "B_Username",
				"title": "Username"
			}, {
				"mDataProp": "B_Passwort",
				"title": "Passwort"
			} ],

		
    	} );
    	
    
		});
		</script>

							<thead>
								<tr>
									<th>ID</th>
									<th>Bezeichnung</th>
									<th>Typ</th>
									<th>Aktionen</th>
								</tr>
							</thead>
							
							
					<table id="example" class="display" cellspacing="0" width="100%">

					</table>


	</div>