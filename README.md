Magento GLUU SSO extension 
=========================
![image](https://raw.githubusercontent.com/GluuFederation/gluu-magento-sso-login-extension/master/plugin.png)

MAGENTO-GLUU-SSO extension gives access for login to your Magento site, with the help of GLUU server.

There are already 2 versions of MAGENTO-GLUU-SSO (2.4.2.0 and 2.4.3.0) extensions, each in its turn is working with oXD and GLUU servers.
For example if you are using MAGENTO-gluu-sso-2.4.2.0 extension, you need to connect with oXD-server-2.4.2.

Now I want to explain in details how to use extension step by step. 

Extension will not be working if your host does not have https://. 

## Step 1. Install Gluu-server 

(version 2.4.2 or 2.4.3)

If you want to use external gluu server, You can not do this step.   

[Gluu-server installation gide](https://www.gluu.org/docs/deployment/).

## Step 2. Download oXD-server 

(version 2.4.2 or 2.4.3)

[Download oXD-server-2.4.2.Final](https://ox.gluu.org/maven/org/xdi/oxd-server/2.4.2.Final/oxd-server-2.4.2.Final-distribution.zip).

or

[Download oXD-server-2.4.3.DEMO](https://ox.gluu.org/maven/org/xdi/oxd-server/2.4.3-SNAPSHOT/oxd-server-2.4.3-SNAPSHOT-distribution.zip).

## Step 3. Unzip and run oXD-server
 
1. Unzip your oXD-server. 
2. Open the command line and navigate to the extracted folder in the conf directory.
3. Open oxd-conf.json file.  
4. If your server is using 8099 port, please change "port" number to free port, which is not used.
5. Set parameter "op_host":"Your gluu-server-url (internal or external)"
6. Open the command line and navigate to the extracted folder in the bin directory.
7. For Linux environment, run sh oxd-start.sh&. 
8. For Windows environment, run oxd-start.bat.
9. After the server starts, go to Step 4.

## Step 4. Download Magento-gluu-sso extension
 
(version 2.4.2 or 2.4.3)

[Download Magento-gluu-sso-2.4.2.0 extension](https://raw.githubusercontent.com/GluuFederation/gluu-magento-sso-login-extension/master/Magento_gluu_SSO_2.4.2.0/Magento_gluu_SSO-2.4.2.0.tgz).

or

[Download Magento-gluu-sso-2.4.3.0 extension](https://raw.githubusercontent.com/GluuFederation/gluu-magento-sso-login-extension/master/Magento_gluu_SSO_2.4.3.0/Magento_gluu_SSO-2.4.3.0.tgz).

For example if you are using gluu-server-2.4.2 it is necessary to use oXD-server-2.4.2 and Magento-gluu-sso-2.4.2.0-extension

## Step 5. Disable cache
 
1. Open menu tab System/Cache Management
![Management](https://raw.githubusercontent.com/GluuFederation/gluu-magento-sso-login-extension/master/docu/mag0.png) 

2. Check select all, set action on disable and click on submit button. 
![submit](https://raw.githubusercontent.com/GluuFederation/gluu-magento-sso-login-extension/master/docu/mag1.png) 

## Step 6. Install extension
 
1. Open menu tab System/Magento Connect/Magento Connect Manager
![Manager](https://raw.githubusercontent.com/GluuFederation/gluu-magento-sso-login-extension/master/docu/mag2.png) 

2. Choose downloaded file and click on upload button. 
![upload](https://raw.githubusercontent.com/GluuFederation/gluu-magento-sso-login-extension/master/docu/mag3.png) 

3. See Auto-scroll console contents, if extension successfully installed return to admin panel.

###Extension will be automatically activated.

3. Open menu tab Gluu SSO/Gluu and Social Login
![GluuSSO](https://raw.githubusercontent.com/GluuFederation/gluu-magento-sso-login-extension/master/docu/mag4.png) 

## Step 7. General

![General](https://raw.githubusercontent.com/GluuFederation/gluu-magento-sso-login-extension/master/docu/1.png)  

1. Admin Email: please add your or admin email address for registrating site in Gluu server.
2. Port number: choose that port which is using oxd-server (see in oxd-server/conf/oxd-conf.json file).
3. Click next to continue.

If You are successfully registered in gluu server, you will see bottom page.

![Oxd_id](https://raw.githubusercontent.com/GluuFederation/gluu-magento-sso-login-extension/master/docu/2.png)

For making sure go to your gluu server / OpenID Connect / Clients and search  Your oxd id

If you want to reset configurations click on Reset configurations button.

## Step 8. OpenID Connect Configuration

OpenID Connect Configuration page for Magento-gluu-sso 2.4.2.0 and Magento-gluu-sso 2.4.3.0 are different.

### Scopes.
You can look all scopes in your gluu server / OpenID Connect / Scopes and understand the meaning of  every scope.
Scopes are need for getting loged in users information from gluu server.
Pay attention to that, which scopes you are using that are switched on in your gluu server.

In Magento-gluu-sso 2.4.2.0  you can only enable, disable and delete scope.
![Scopes1](https://raw.githubusercontent.com/GluuFederation/gluu-magento-sso-login-extension/master/docu/3.png) 

In Magento-gluu-sso 2.4.3.0 you can not only enable, disable and delete scope, but also add new scope, but when you add new scope by {any name}, necessary to add that scop in your gluu server too. 
![Scopes2](https://raw.githubusercontent.com/GluuFederation/gluu-magento-sso-login-extension/master/docu/4.png) 

### Custom scripts.

![Customscripts](https://raw.githubusercontent.com/GluuFederation/gluu-magento-sso-login-extension/master/docu/5.png)  

You can look all custom scripts in your gluu server / Configuration / Manage Custom Scripts / and enable login type, which type you want.
Custom Script represent itself the type of login, at this moment gluu server supports (U2F, Duo, Google +, Basic) types.

### Pay attention to that.

1. Which custom script you enable in your Magento site in order it must be switched on in gluu server too.
2. Which custom script you will be enable in OpenID Connect Configuration page, after saving that will be showed in Magento Configuration page too.
3. When you create new custom script, both fields are required.

## Step 9. Magento Configuration

### Customize Login Icons
 
Pay attention to that, if custom scripts are not enabled, nothing will be showed.
Customize shape, space between icons and size of the login icons.

![WordpressConfiguration](https://raw.githubusercontent.com/GluuFederation/gluu-magento-sso-login-extension/master/docu/6.png)  

## Step 10. Show icons in frontend

Go to https://{site-base-url}/index.php/customer/account/login/

![frontend](https://raw.githubusercontent.com/GluuFederation/gluu-magento-sso-login-extension/master/docu/7.png) 