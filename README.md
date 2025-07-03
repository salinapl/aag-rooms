# aag-rooms - Library Room Reservation status display using Kirby
A Simple and flexible room reservation status display for e-ink or other non-touch displays using [Kirby](https://getkirby.com)

![example](https://github.com/user-attachments/assets/18a9217b-5817-4ca8-9a20-8d3d1fd7c1ba)

## Features

- Closing Hour Override per-room
- Per-room toggleable notice text display
- Per-room QR code display (must be generated elsewhere and uploaded to interface at this time)
- Ships with integration of LibraryMarket's LibraryCalendar App
- Can use any event calendar app that has JSON event API with some tweaking
- Timeline of upcoming events, hides private event titles but keeps event ID for refrence
- Overflowed events past timeline window are shown at bottom
- Alternative schedule view that shows all events for the day in a static schedule
- Ships with e-ink friendly fonts and CSS configs, designed to work on a [TRMNL](https://usetrmnl.com/) device 800x480 screens.

## Planned Features

- Per-Room Color or monochrome CSS
- Per-Room description that displays when no upcoming events in timeline

## Download and Install

This repository only contains the content pages of the site, you will need to download the latest Kirby plainkit seprately.

1. Before Starting, please check that your webserver meets Kirbys minimum requirements **[listed here](https://getkirby.com/docs/guide/quickstart#requirements)** and read the provided getting started documentation.
1. Download the latest release of **[Kirby Plainkit](https://github.com/getkirby/plainkit)**
1. Extract the plainkit to your Website Folder
1. Download the latest release of LibSignTool from the **[releases page](https://github.com/salinapl/LibSignTool/releases)**
1. Extract LibSignTool into the plainkit-main folder
1. Some files may ask to be overwritten, approve all overwrites.
1. Make sure hidden files such as .htaccess copied over, as these are required for the site to operate correctly.
1. Start your webserver and navigate to **yourdomain.example.com/location-of-kirbycms-install/panel** and you will be asked to create an account.
1. After creating the account, you will be able to log in and start adding images to create a campaign. The download includes example pages to get started, but you can edit or remove these pages as long as you replace them with ones using the same or similar templates. Doing more than that will require knowledge of how Kirby works. Examples of what templates do what will be provided later in this document.

## Backing up and Installing new Versions

### Kirby
Kirby is a Flat-file CMS and does not require a database, which makes it very easy to
install and backup. Just copy the folder you installed Kirby and LibSignTool to into your backup location to back it up.

To upgrade Kirby, simply download the newest version of the plainkit, Delete the "kirby" and "media" folders from your install folder, and copy the new versions from the plainkit into the folder. Always refer to the offical Kirby documentation for upgrade instructions as these are subject to change between releases.

LibSignTool is built on Kirby 3. Staying within the same generation of releases should be fine, but wait for offical word before upgrading to possible future KirbyCMS generations such as Kirby 4

#### Kirby 5
Kirby 5 is now fully supported, Kirby 4 has only been tested up to v4.8.0

## A Note about Licensing

While aag-rooms is provided free under the MIT License, Kirby is not.

You can try Kirby on your local machine or on a test
server as long as you need to make sure it is the right
tool for your next project.

However Production use requires a License Key.

### Buying a license

You can purchase your Kirby license at
<https://getkirby.com/buy>

A Kirby license is valid for a single domain. You can find
Kirby's license agreement here: <https://getkirby.com/license>

You can learn more about Kirby at [getkirby.com](https://getkirby.com).

### Kirby Documentation

<https://getkirby.com/docs>

### Kirby Support

<https://getkirby.com/support>
    
## Issues

We do not develop for Kirby, for issues getting Kirby up and running, please contact that project. We only provide the website content files to host on Kirby to use as a Digital Signage Platform.

If you have a Github account, please report issues directly on Github: <https://github.com/salinapl/LibSignTool/issues>

Or you can email the maintainers directly at <tech.lib@salinapublic.org>
