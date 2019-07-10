# Argus Solution

![Architecture](docs/Architecture.png)

The **Argus Solution** is made of the following software components:
+ **The Argus Client**. It is the Android native mobile application used by healthcare facilities to send alerts and reports through SMS.
+ **The Argus Gateway**. It is an Android native mobile application installed on an unattended phone to manage the reception and sending of SMS and share them with the Argus server. It can be installed on multiple Android phones to support more SMS traffic.
+ **The Argus Config**. It is a PHP web application processing all incoming and outgoing SMS. It also provides a diagnostic web page showing the status of all connected gateways. 
+ **The Argus Dashboard**. It is a PHP web application used to configure the system, manage the user access, display alerts and validate the reports.
+ **The Argus Dashboard Report**. It is a PHP web application providing a set of canned outputs built with PHP Report.
+ **The Argus Angular Dashboard**. It is a modern Angular web application facilitating the validation of the reports by exposing a mobile friendly user interface.
