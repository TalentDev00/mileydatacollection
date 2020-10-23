<?php
/**
 * Created by PhpStorm.
 * User: evan
 * Date: 12/14/17
 * Time: 12:46 AM
 */
require_once('../../../includes/adminGlobals.php');

ForceSecureURL();
CheckAdminSecurity('M');
BlockCSRFFailingRequest();

switch(Request('a')) {
    case 'addLeadGroup':
      $em = \SCMiley\Database\DoctrineEntityManager::get();
      $leadGroup = new \SCMiley\Entities\LeadGroup(SRT('coreTerm'), SRT('modifiers'), SRT('searchGeos'), SRT('email'));
      $em->persist($leadGroup);
      $em->flush();
      if($leadGroup) {
          echo 'ok:' . $leadGroup->getID();
      } else {
         echo 'Sorry, something went wrong. Please contact support.';
      }
      break;
    default:
      DrawAddLeadGroupPage();
}
CloseDBConnection();
exit();

function DrawAddLeadGroupPage() {
    BeginAdminPage('Add a Lead Group');
    ?>
    <script>
        window.addEvent('domready', function() {
            $('leadGroupForm').addEvent('submit', addLeadGroup);
        });
        function addLeadGroup(event) {
            event.preventDefault();
            theForm = this;
            if(!confirmNonEmpty(theForm.coreTerm, 'Please indicate the core term.')) return false;

            showLoad();
            new Request.HTML({ url: App.thisPage + '?a=addLeadGroup',
                method: 'post',
                data: theForm,
                onComplete: function() {
                    hideLoad();
                    var LeadGroupID = this.response.text.substring(3); // probably, unless we got an error message back
                    if(this.response.text.substring(0, 3) == 'ok:') {
                        window.location.href = 'leadGroup.php?ID=' + LeadGroupID;
                    } else if(this.response.text.substring(0, 3) == 'du:') {
                        if(confirm('Actually, a leadGroup already exists with this name.\n\n' +
                                'Would you like to go and see it now?'))
                            window.location.href = 'leadGroup.php?ID=' + LeadGroupID;
                    } else {
                        alert(this.response.text);
                    }
                }
            }).send();
        }
    </script>

    <style>
        #header {
            background: transparent url(images/logo.png) center 10px no-repeat !important;
        }
    </style>
    <h1>Add New Lead Group</h1>
    <?
      echo \SCMiley\Entities\LeadGroup::getFormHTML();

    EndAdminPage();
}
