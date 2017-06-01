
<script src="http://hourlylancer.com/aditi/aws-amazone/js/aws-cognito-sdk.min.js"></script>
<script src="http://hourlylancer.com/aditi/aws-amazone/js/amazon-cognito-identity.min.js"></script>
<script src="https://sdk.amazonaws.com/js/aws-sdk-2.24.0.min.js"></script>


<script>

      var authenticationData = {
           Username : 'gurkirat_21',
           Password : 'Guri123!@#',
       };
       var authenticationDetails = new AWSCognito.CognitoIdentityServiceProvider.AuthenticationDetails(authenticationData);
       var poolData = {
           UserPoolId : 'us-east-1_wucM12PqM',
           ClientId : '359tbb8e774gft2ugia4aibd52'
       };
       var userPool = new AWSCognito.CognitoIdentityServiceProvider.CognitoUserPool(poolData);
       var userData = {
           Username : 'gurkirat_21',
           Pool : userPool
       };
       var cognitoUser = new AWSCognito.CognitoIdentityServiceProvider.CognitoUser(userData);
	   
       console.log(cognitoUser); 
 
       cognitoUser.authenticateUser(authenticationDetails, {
           onSuccess: function (result) {
               console.log('access token + ' + result.getAccessToken().getJwtToken());

               AWS.config.credentials = new AWS.CognitoIdentityCredentials({
                   IdentityPoolId : 'us-east-1_wucM12PqM', // your identity pool id here
                   Logins : {
                       // Change the key below according to the specific region your user pool is in.
                       'cognito-idp.us-east-1.amazonaws.com/us-east-1_wucM12PqM' : result.getIdToken().getJwtToken()
                   }
               });
           }

       });

</script>

