[TOC]

#Magento OpenID Connect SSO Extension By Gluu  

![image](https://raw.githubusercontent.com/GluuFederation/gluu-magento-sso-login-extension/master/plugin.jpg)

Gluu's Magento OpenID Connect Single Sign On (SSO) Extension will enable you to authenticate users against any standard OpenID Connect Provider (OP). If you don't already have an OP you can [deploy the free open source Gluu Server](https://gluu.org/docs/deployment).  

## Requirements
In order to use the Magento Extension, you will need to have deployed a standard OP like the Gluu Server and the oxd Server.

* [Gluu Server Installation Guide](https://www.gluu.org/docs/deployment/).

* [oxd Server Installation Guide](https://oxd.gluu.org/docs/oxdserver/install/)

## Installation

### Step 1. Download

[Link to Magento marketplace](https://www.magentocommerce.com/magento-connect/openid-connect-sso.html)
 
[Github source](https://github.com/GluuFederation/gluu-magento-sso-login-extension/blob/master/Magento_gluu_SSO-2.4.4.tgz?raw=true).

### Step 2. Disable cache
 
1. Open menu tab System/Cache Management
![Management](https://raw.githubusercontent.com/GluuFederation/gluu-magento-sso-login-extension/master/docu/mag0.png) 

2. Check select all, set action on disable and click on submit button. 
![submit](https://raw.githubusercontent.com/GluuFederation/gluu-magento-sso-login-extension/master/docu/mag1.png) 

### Step 3. Install extension
 
1. Open menu tab System/Magento Connect/Magento Connect Manager
![Manager](https://raw.githubusercontent.com/GluuFederation/gluu-magento-sso-login-extension/master/docu/mag2.png) 

2. Choose downloaded file and click on upload button. 
![upload](https://raw.githubusercontent.com/GluuFederation/gluu-magento-sso-login-extension/master/docu/mag3.png) 

3. See Auto-scroll console contents, if extension successfully installed return to admin panel.

####Extension will be automatically activated.

3. Open menu tab OpenID Connect SSO By Gluu / Open extension page
![GluuSSO](https://raw.githubusercontent.com/GluuFederation/gluu-magento-sso-login-extension/master/docu/44.mag4.png) 

### Step 4. General

![General](https://raw.githubusercontent.com/GluuFederation/gluu-magento-sso-login-extension/master/docu/44.m1.png)  

1. Admin Email: please add your or admin email address for registrating site in Gluu server.
2. Gluu Server URL: please insert your Gluu server URL.
3. Port number: choose that port which is using oxd-server (see in oxd-server/conf/oxd-conf.json file).
4. Click next to continue.

If You are successfully registered in gluu server, you will see bottom page.

![Oxd_id](https://raw.githubusercontent.com/GluuFederation/gluu-magento-sso-login-extension/master/docu/44.m2.png)

To make sure everything is configured properly, login to your Gluu Server and navigate to the OpenID Connect > Clients page. Search for your `oxd id`.

### Step 5. OpenID Connect Provider (OP) Configuration

#### Scopes.
Scopes are groups of user attributes that are sent from your OP (in this case, the Gluu Server) to the application during login and enrollment. You can view all available scopes in your Gluu Server by navigating to the OpenID Connect > Scopes intefrace. 

In the Extension interface you can enable, disable and delete scopes. You can also add new scopes. If/when you add new scopes via the extension, be sure to also add the same scopes in your gluu server. 
![Scopes2](https://raw.githubusercontent.com/GluuFederation/gluu-magento-sso-login-extension/master/docu/44.m4.png) 

#### Authentication.
To specify the desired authentication mechanism navigate to the Configuration > Manage Custom Scripts menu in your Gluu Server. From there you can enable one of the out-of-the-box authentication mechanisms, such as password, U2F device (like yubikey), or mobile authentication. You can learn more about the Gluu Server authentication capabilities in the [docs](https://gluu.org/docs/multi-factor/intro/).
![Customscripts](https://raw.githubusercontent.com/GluuFederation/gluu-magento-sso-login-extension/master/docu/44.m5.png)  

Note:    
- The authentication mechanism specified in your Magento extension page must match the authentication mechanism specified in your Gluu Server.     
- After saving the authentication mechanism in your Gluu Server, it will be displayed in the Magento extension configuration page too.      
- If / when you create a new custom script, both fields are required.    


### Step 6. Magento Configuration

#### Customize Login Icons
 
If custom scripts are not enabled, nothing will be showed. Customize shape, space between icons and size of the login icons.

![MagentoConfiguration](https://raw.githubusercontent.com/GluuFederation/gluu-magento-sso-login-extension/master/docu/44.m6.png)  

### Step 7. Show icons in frontend

Once you've configured all the options, you should see your supported authentication mechanisms on your default Magento customer login page like the screenshot below

Go to https://{site-base-url}/index.php/customer/account/login/

![frontend](https://raw.githubusercontent.com/GluuFederation/gluu-magento-sso-login-extension/master/docu/44.m7.png) 
