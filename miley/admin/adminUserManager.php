<?php
require_once('../../../includes/adminGlobals.php');
/*=======================================================================
' adminUserManager.php
' jpl 7/15/11
'
'
'======================================================================*/

	CheckPageAdminSecurity();
	
	$roleNameMap = array(
		'M' => 'Manager',
		'A' => 'Agent',
		'L' => 'Loader',
        'T' => 'Templates',
        'I' => 'Interdependence'
	);
	
	switch(Request("a")) {
		case 'add':					AddAdminUser();		 DrawAdminUsers();					break;
		case 'loadAdminUserEditor':	DisplayAdminUserEditor(RI("AdminUserID"));				break;
		case "save":				UpdateAdminUser(RI("AdminUserID")); DrawAdminUsers();	break;
		case 'delete':				DeleteAdminUserFR(RI("AdminUserID"));					break;
		case "loadAdminUsers":		DrawAdminUsers();										break;
		default:					DisplayAdminUserManagerPage();
	}
	CloseDBConnection();
	exit();

function AddAdminUser() {
	$username = UniqueAdminUsername(SRT('username'));
	$nameFields = ParseNameFieldsFromFullName(SRT('name'));
	$AdminUserID = ExecuteInsert(
		"INSERT INTO tblAdminUsers
			   (username, firstName, lastName, passwordHash)
		 VALUES(" . SQLValue($username) . ", 
			    " . SQLValue($nameFields['firstName']) . ",
			    " . SQLValue($nameFields['lastName']) . ", '3wnrindldk')");
		
	echo "<script>summonAdminUserEditor($('addNewPopUpHook'), $AdminUserID, " . JSValue(SRT('username')) . ");</script>";
}

function DeleteAdminUserFR($AdminUserID) {
	if($AdminUserID != CurrentAdminUserID()) {
		ExecuteDelete('tblAdminUsers', $AdminUserID);
	}
}



function UpdateAdminUser($AdminUserID) {
	$roles = implode('', RequestArray('roles'));
	ExecuteUpdateFromRequest('tblAdminUsers', $AdminUserID, "firstName:50 lastName:50 email:50 isActive:c roles:S$roles");
	if(Request('password') != ''  &&  Request('password') == Request('password2')) {
		ExecuteUpdate('tblAdminUsers', $AdminUserID, 'passwordHash:S' . sha1(Request('password')));
	}
}


function DisplayAdminUserManagerPage() {
	
	BeginAdminPage('Configure Admin Users', false);
?>
<script type="text/javascript">
window.addEvent('domready', function() {
	
});
	function summonAdminUserEditor(theLink, AdminUserID, userName) {
		App.popUp.setPosition({
			relativeTo: theLink,
			offset: { x: -300, y: -100 }
		});
		App.popUp.openURL(App.thisPage + '?a=loadAdminUserEditor&AdminUserID=' + AdminUserID, 'Edit ' + userName);
	}
	function updateAdminUser(theForm) {
		new Request.HTML({ url: App.thisPage + '?a=save',
			method: 'post',
			data: theForm,
			update: 'listingDiv',
			onComplete: function() {
				App.roar.alert('The admin user is updated.');
				App.popUp.close();
			}
		}).send();
	}
	function addAdminUser(theForm) {
		new Request.HTML({ url: App.thisPage + '?a=add',
			method: 'post',
			data: theForm,
			update: 'listingDiv',
			evalScripts: true
		}).send();
	}
	function deleteAdminUser(AdminUserID, theButton) {
		if(!confirm('Are you certain you wish to delete this user?')) return;
		new Request.HTML({ url: App.thisPage + '?a=delete',
			method: 'post',
			data: { AdminUserID: AdminUserID },
			onComplete: function() {
				$('userRow' + AdminUserID).dispose();
				$(theButton).closeParentPopUp();
				App.roar.alert('The user has been deleted.');
			}
		}).send();
	}
	
</script>
  <h2>Manage Admin Users</h2>
  <div id="listingDiv"><?=DrawAdminUsers()?></div>
  <a name="addNewPopUpHook" class="right"></a>
  <form method="post" action="JavaScript:void(0);" onsubmit="JavaScript: addAdminUser(this);" class="nextSection box">
  	<h2>Add new user:</h2>
    Name: <input type="text" name="name" style="width: 250px;" />
    Username: <input type="text" name="username" style="width: 150px;" />
    <input type="submit" value="Add" />
  </form>
<?
	EndAdminPage();
}
function DrawAdminUsers() {
?>
  <table class="data nextElement" id="listingTable">
  	<tr>
  	  <th width="140">Roles</th>
  	  <th width="170">Name</th>
  	  <th width="220">Email</th>
  	  <th width="60">Active?</th>
  	  <th width="60">Logins</th>
  	  <th width="140">Last Login Date</th>
  	  <th width="50"></th>
  	</tr>
<?
	$auRS = GetRS(
		"SELECT ID, CONCAT(firstName, ' ', lastName) AS name, email, roles, isActive, loginCount, lastLoginDate
		   FROM tblAdminUsers
		  ORDER BY firstName");
	while($auR = mysqli_fetch_assoc($auRS)) { ?>
	<tr id="userRow<?=$auR['ID']?>">
	  <td><?=GetAdminUserRolesList($auR['roles'])?></td>
	  <td><?=$auR['name']?></td>
	  <td><?=$auR['email']?></td>
	  <td><?=(RSBool($auR['isActive']) ? 'Yes' : '')?></td>
	  <td class="right"><?=$auR['loginCount']?></td>
	  <td><?=FormatRSDate('m/d/y @ g:ia', $auR['lastLoginDate'])?></td>
	  <td><a href="JavaScript:void(0);" id="editLink<?=$auR['ID']?>" 
	  	onclick="JavaScript: summonAdminUserEditor(this, <?=$auR['ID']?>, <?=JSValue($auR['name'])?>);">edit</a></td>
	</tr>
<? 	} ?>
  </table>
<?
}

function DisplayAdminUserEditor($AdminUserID) {
	if(!$auR = GetR(
		"SELECT ID, username, firstName, lastName, roles, email, isActive, loginCount, lastLoginDate
		   FROM tblAdminUsers
		  WHERE ID=$AdminUserID")) return;
	global $roleNameMap;
?>
<form action="JavaScript:void(0);" onsubmit="JavaScript: updateAdminUser(this);">
  <input type="hidden" name="AdminUserID" value="<?=$AdminUserID?>" />
  <table>
  	<tr><td>Username:</td><td><?=$auR['username']?></td></tr>
  	<tr><td>First Name:</td><td><?=RSTextInput($auR, 'firstName', 50, 300)?></td></tr>
  	<tr><td>Last Name:</td><td><?=RSTextInput($auR, 'lastName', 50, 300)?></td></tr>
  	<tr><td>Roles:</td>
  	  <td>
<?	foreach($roleNameMap as $role => $label) {
		echo CheckboxInput('roles[]', strpos($auR['roles'], $role) !== false, $label, array('value' => $role)) . "<br />";
	}
?>
  	  </td>
  	</tr>
  	<tr><td>Email:</td><td><?=RSTextInput($auR, 'email', 50, 300)?></td></tr>
  	<tr><td>Password:</td><td><?=RSPasswordInput($auR, 'password', 50, 300)?></td></tr>
  	<tr><td>Confirm:</td><td><?=RSPasswordInput($auR, 'password2', 50, 300)?></td></tr>
  	<tr><td>Is Active:</td><td><?=CheckboxInput('isActive', RSBool($auR['isActive']))?></td></tr>
  	<tr>
  	  <td></td>
  	  <td>
  	  	<input type="submit" value="Save" />
  		<? if($auR['loginCount'] == 0) { echo JSLink('delete', "deleteAdminUser($AdminUserID, this);", 'button jumbo'); } ?>
  	  </td>
  	</tr>
  </table>
</form>
<?
}


function GetAdminUserRolesList($roles) {
	global $roleNameMap;
	$resultSet = array();
	for($i = 0; $i < strlen($roles); $i++) {
		$resultSet[] = $roleNameMap[substr($roles, $i, 1)];
	}
	return implode(', ', $resultSet);
}

