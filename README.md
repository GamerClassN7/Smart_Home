<p align="center">
  <img src="./templates/images/icon-512x512.png" height="100" width="100">
</p>

# Smart_Home
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![Discord](https://img.shields.io/discord/601817042475286540.svg?color=Blue&label=Discord&logo=Discord)](https://discord.gg/nMe5evu)

PHP, JS, HTML - Supports PWA


# Installation
default user is Admin and his password id ESP

# Discord
https://discord.gg/nMe5evu

## Browser (Desktop PWA)

<img src="./_README_IMG/1.png" height="500" width="1000">
<img src="./_README_IMG/2.png" height="500" width="1000">
<img src="./_README_IMG/3.png" height="500" width="1000">
<img src="./_README_IMG/4.png" height="500" width="1000">
<img src="./_README_IMG/5.png" height="500" width="1000">

## Mobile (PWA)

<img src="./_README_IMG/6.png" height="500" width="250">
<img src="./_README_IMG/7.png" height="500" width="250">
<img src="./_README_IMG/8.png" height="500" width="250">
<img src="./_README_IMG/9.png" height="500" width="250">
<img src="./_README_IMG/10.png" height="500" width="250">

API
POST Message (Spínač)
```
{
	"token":"2"
}
```
Answer (Spínač)
```
{
	"device":{
		"hostname":"2",
		"sleepTime":0
		},
		"state":"succes",
		"value":"0"
	}
}
```
POST Message (Sensor)
```
{
	"token":"4",
	"values":{
		"door":{
			"value":1
		}
	}
}
```
Answer (Sensor)
```
{

}
```
