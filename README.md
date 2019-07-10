# moodle-local_eduauth
Alternative Authentication System for Moodle

This authentication systems works a little different from the default implementation of moodle and its use is technically very stable. This authentication plugin can be used by various apps at it differentiates between several apps when storing a token.

The interface is rather simple.

## Login
Just open the following URL (Systembrowser, In-App-Browser or iFrame): `{wwwroot}/local/eduauth/login.php?token={yourtoken}&appid={yourappid}`

{yourtoken} has to be replaced with a token that you created, max. 40 chars
{yourappid} should identify your app clearly, max. 40  chars, and has to be used anytime you want to retrieve data.

Once you started the login procedure, your app can ask Moodle if the user already logged in using this token. This can be done regularly (e.g. every 500ms-1s or as soon as your app is restarted). For this you can use the URL `{wwwroot}/local/eduauth/login.php?token={yourtoken}&appid={yourappid}&act=getuser`.

If the user has logged in you will retrieve a JSON-encoded answer with the following data:
```
{
    'appid' => {yourappid},
    'token' => {yourtoken},
    'sitename' => {the fullname of the moodle site},
    'userid' => {the user id},
    'wwwroot' => {the wwwroot of the moodle site},
}
```

You have to store at least the userid and the token persistently to make further calls.

## Open a Moodle-Site as user

Just open the system browser using the following url:
`{$CFG->wwwroot}/local/eduauth/launch.php?userid={theuserid}&token={yourtoken}&appid={yourappid}&url={themoodleurl}`

`{themoodleurl}` should be the desired URL of a page within Moodle in base64 encoded format.

## Retrieve data via JSON
### Basic call
Just make ajax-calls against `{$CFG->wwwroot}/local/eduauth/connect.php` providing the following parameters:

```
{
    'appid' => {yourappid},
    'token' => {yourtoken},
    'userid' => {the user id},
    'act' => {the action},
    {optional additional data according to act}
}
```

### Possible actions
#### callForward
If you created a moodle plugin for your own purpose you can forward calls to this plugin.

Provide the following (*additional*) parameter to the basic call:
```
{
    'callforward' => {the name of your plugin in frankenstyle},
}
```

Create a PHP-Script inside your plugins root-folder and name it 'eduauth.php'. Define the class {plugintype}_{yourpluginname}_eduauth that should have on static method called 'callforward' that takes the two parameters $data and &$reply.

You can attach any data in $reply that will be sent to your app in JSON-Format.

Example:
In this example we implement custom functionality for the plugin *block_something* in the file */blocks/something/eduauth.php*:

```
class block_something_eduauth {
    public static function callforward($data, &$reply) {
        if (!empty($data->yeswecan)) {
            $reply->yescandoit = true;
        } else {
            $reply->error = "sorry, we can't";
        }
    }
}
```


#### myData
Retrieves data about the user himself.

Requires no additional parameters and returns a JSON-encoded string with the following data:
```
{
    'email' => {users email},
    'firstname' => {users firstname},
    'lastname' => {users lastname},
    'pictureurl' => {url to users picture - does maybe only work when used with a wstoken},
    'userid' => {the userid},
    'username' => {the users login name},
}
```

#### removeMe
Removes tokens from this plugin and can be used to remove any tokens from that user, for a specific app only, or a specific token only.

Provide the following (*additional*) parameters to the basic call:
```
{
    'onlyappid' => {(optional) yourappid - removes only tokens from your app},
    'onlytoken' => {(optional) yourtoken - remove only a particular token},
}
```

Returns a JSON-encoded string with the following data:
```
{
    'status' => 'ok',
}
```

#### wstoken
Creates a moodle mobile webservice token using the default moodle api.

Returns a JSON-encoded string with the following data:
```
{
    'wstoken' => {the moodle mobile webservice token},
}
```
