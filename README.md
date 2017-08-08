# Simple Auth0 Login
## Wordpress Auth0 Plugin alternative

This is a work in progress, it's in response to the needs that the official plugin wasn't meeting for us.

- WORKING
  - Overrides Authentication and creates users from Auth0
  - Settings to enter API Key
  - Full password reset
  - Signup
  - Validated the action used in the original plugin works

- NOT IMPLEMENTED / UNTESTED
  - Move all JS to one main file instead of views.
  - Add back door similar to other plugin wple query string
  - Update Auth0 email from profile
  - Store Token in Users Meta?
  - Add Single SignOut

- NOT IMPLEMENTED / OUR REQUIREMENTS
  - Need to check if user exists when user tries to reset password, if user doesn't exist and sign up is enabled can you send them to sign up?
