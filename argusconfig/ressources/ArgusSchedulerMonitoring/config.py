# Configuration file for Monitoring Scheduler #
# ------------------------------------------- #
# Version
version = 'v1.0 - 12/09/2016'

# Interval scheduler in seconds
interval = 60

# Url Diagnosis Page
url_diagnosis_page =  "http://localhost/ses/index.php?load_url=page_diagnosis.php"

# Url Surveillance page
url_surveillance_page =  "http://localhost/ses/action_run_tasks.php"

# Text string Diagnosis OK
diagnosis_ok = 'ALL_DIAGNOSIS_OK'

# Icons & sounds
icon_default = 'icones\Icone.ico'
icon_success = 'icones\icone-ok.ico'
icon_error = 'icones\icone-pb.ico'
wav_alarm = 'alarme.wav'

# Play / not play alarm
play_alarm = False

# SMTP configuration
# Smtp Enabled
smtp_Enabled = False # False

# TLS Enabled : 1 , Port 587
smtp_TLS = 0
# SSL Enabled : 1 , Port 465
smtp_SSL = 1

#smtp_host = 'auth.myhosting.com'
#smtp_host = 'localhost'
smtp_host = 'smtp.gmail.com'
smtp_port = 465
smtp_login = 'argus@gmail.com'
smtp_password = 'xxxxx'

# Mail configuration
mail_subject = 'Argus Server - Issue Notification'
mail_from = 'argus@gmail.com'
# Array 
mail_to = ['xxx@xxx.xx', 'xxx@xxx.xx', 'xxx@xxx.xx']
mail_body = '<font face="Calibri, Times New Roman, Courier"> The Argus Server</a> is currently experiencing an error. <br/>Please go to the server diagnostic page for additional details<br/><br/> You are receiving this message because you are configured as a server issue notification recipient. If you do not wish to receive these notifications please contact an ARGUS administrator. <br/><br/> For support, comments or questions please contact <a href="mailto:argus@novel-t.ch">argus@novel-t.ch</a></font>'

# Mail interval (minutes)
mail_interval = 60

# Mail Test configuration
mail_test_subject = 'ARGUS : This is a test'
mail_test_from = 'argus@gmail.com'
#  Array
mail_test_to = ['xxx@gmail.com']
mail_test_body = 'ARGUS : This is a test'


