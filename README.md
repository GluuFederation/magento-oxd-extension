[TOC]

# OpenID Connect Single Sign-On (SSO) Magento Extension By Gluu

![image](https://raw.githubusercontent.com/GluuFederation/gluu-magento-sso-login-extension/master/plugin.jpg)

Gluu's OpenID Connect Single Sign-On (SSO) Magento Extension will enable you to authenticate users against any standard OpenID Connect Provider (OP). If you don't already have an OP you can use Google or [deploy the free open source Gluu Server](https://gluu.org/docs/deployment).

## Requirements
In order to use the Magento Extension you will need a standard OP (like Google or a Gluu Server) and the oxd server.

* [Gluu Server Installation Guide](https://www.gluu.org/docs/deployment/).

* [oxd Webpage](https://oxd.gluu.org)


## Installation

### Disable cache

1. Open menu tab System/Cache Management
![Management](https://raw.githubusercontent.com/GluuFederation/gluu-magento-sso-login-extension/master/docu/mag0.png)

2. Check select all, set action on disable and click on submit button.
![submit](https://raw.githubusercontent.com/GluuFederation/gluu-magento-sso-login-extension/master/docu/mag1.png)

### Download

[Link to Magento marketplace](https://www.magentocommerce.com/magento-connect/openid-connect-sso.html)

[Github source](https://raw.githubusercontent.com/GluuFederation/gluu-magento-sso-login-extension/blob/master/Magento_gluu_SSO-2.4.4.tgz).

### Install extension

1. Open menu tab System/Magento Connect/Magento Connect Manager
![Manager](https://raw.githubusercontent.com/GluuFederation/gluu-magento-sso-login-extension/master/docu/mag2.png)

2. Choose downloaded file and click on upload button.
![upload](https://raw.githubusercontent.com/GluuFederation/gluu-magento-sso-login-extension/master/docu/mag3.png)

3. See Auto-scroll console contents, if extension successfully installed return to admin panel.

####Extension will be automatically activated.

3. Open menu tab OpenID Connect/ Open extension page
![GluuSSO](https://raw.githubusercontent.com/GluuFederation/gluu-magento-sso-login-extension/master/docu/44.mag4.png)


## Configuration

### General

In your Magento admin menu panel you should now see the OpenID Connect menu tab. Click the link to navigate to the General configuration  page:

![General](https://raw.githubusercontent.com/GluuFederation/gluu-magento-sso-login-extension/master/docu/44.m1.png)  

1. Automatically register any user with an account in the OpenID Provider: By setting registration to automatic, any user with an account in the OP will be able to register for an account in your Magento site. They will be assigned the new user default role specified below.
2. Only register users with the following role(s) in the OP: Using this option you can limit registration to users who have a specified role in the OP, for instance `magento`. This is not configurable in all OP's. It is configurable if you are using a Gluu Server. [Follow the instructions below](#role-based-enrollment) to limit access based on an OP role.
3. New Customer Default Group: specify which group to give to new customer upon registration.
4. URI of the OpenID Provider: insert the URI of the OpenID Connect Provider.
5. Custom URI after logout: custom URI after logout (for example "Thank you" page).
6. oxd port: enter the oxd-server port (you can find this in the `oxd-server/conf/oxd-conf.json` file).
7. Click `Register` to continue.

If your OpenID Provider supports dynamic registration, no additional steps are required in the general tab and you can navigate to the [OpenID Connect Configuration](#openid-connect-configuration) tab.

If your OpenID Connect Provider doesn't support dynamic registration, you will need to insert your OpenID Provider `client_id` and `client_secret` on the following page.

![General](https://raw.githubusercontent.com/GluuFederation/gluu-magento-sso-login-extension/master/docu/44.m1.1.png) 

To generate your `client_id` and `client_secret` use the `Redirect URL` for customer page: `https://{site-base-url}/index.php/customer/account/login/?option=getOxdSocialLogin` and `Redirect URL` for administrator  page: `https://{site-base-url}/index.php/admin/?option=getOxdAdminLogin`.

> If you are using a Gluu server as your OpenID Provider, you can make sure everything is configured properly by logging into to your Gluu Server, navigate to the OpenID Connect > Clients page. Search for your `oxd id`.

#### Role based enrollment

1. Navigate to your Gluu Server admin GUI.
2. Click the `Users` tab in the left hand navigation menu.
3. Select `Manage People`.
4. Find the person(s) who should have access.
5. Click their user entry.
6. Add the `User Permission` attribute to the person and specify the same value as in the extension. For instance, if in the extension you have limit enrollment to user(s) with role = `magento`, then you should also have `User Permission` = `magento` in the user entry. [See a screenshot example](https://raw.githubusercontent.com/GluuFederation/gluu-magento-sso-login-extension/master/docu/permission.png).
7. Update the user record.
8. Go back to the Magento extension and make sure the `permission` scope is requested (see below).
9. Now they are ready for enrollment at your Magento site.

### OpenID Connect Configuration

![General](https://raw.githubusercontent.com/GluuFederation/gluu-magento-sso-login-extension/master/docu/config.png) 

#### User Scopes

Scopes are groups of user attributes that are sent from the OP to the application during login and enrollment. By default, the requested scopes are `profile`, `email`, and `openid`.

To view your OP's available scopes, open a web browser and navigate to `https://OpenID-Provider/.well-known/openid-configuration`. For example, here are the scopes you can request if you're using [Google as your OP](https://accounts.google.com/.well-known/openid-configuration).

If you are using a Gluu server as your OpenID Provider, you can view all available scopes by navigating to the OpenID Connect > Scopes intefrace inside the Gluu Server.

In the extension interface you can enable, disable and delete scopes.

#### Authentication

 Bypass the local Magento customer login page and send users straight to the OP for authentication: Check this box so that when users attempt to login they are sent straight to the OP, bypassing the local Magento customer login screen. When it is not checked, users will see the following screen when trying to login:
![General](https://raw.githubusercontent.com/GluuFederation/gluu-magento-sso-login-extension/master/docu/customer_login.png) 

 Bypass the local Magento administrator login page and send users straight to the OP for authentication: Check this box so that when users attempt to login they are sent straight to the OP, bypassing the local Magento administrator login screen. When it is not checked, users will see the following screen when trying to login:
![General](https://raw.githubusercontent.com/GluuFederation/gluu-magento-sso-login-extension/master/docu/admin_login.png) 

Select ACR: To signal which type of authentication should be used, an OpenID Connect client may request a specific authentication context class reference value (a.k.a. "acr"). The authentication options available will depend on which types of mechanisms the OP has been configured to support. The Gluu Server supports the following authentication mechanisms out-of-the-box: username/password (basic), Duo Security, Super Gluu, and U2F tokens, like Yubikey.

Navigate to your OpenID Provider confiuration webpage `https://OpenID-Provider/.well-known/openid-configuration` to see supported `acr_values`.

In the `Select acr` section of the extension page, choose the mechanism which you want for authentication. If the `Select acr` value in the extension is `none`, users will be sent to pass the OP's default authentication mechanism.

