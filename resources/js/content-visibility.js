jQuery(document).ready(function($) {
  
  toggleMemberVisibility();

  function toggleMemberVisibility() {
    var token = Cookies.get('ccm_token');
    console.log("Token: " + JSON.stringify(token) );
    if ( typeof token === 'undefined' ) {
      console.log( "Did NOT find ccm_token: " + token );
      $('.cm-signed-out').show();
      $('.cm-signed-in').hide();
    }
    else {
      console.log( "Found ccm_token: " + token );
      $('.cm-signed-out').hide();
      $('.cm-signed-in').show();
    }
  }

});

