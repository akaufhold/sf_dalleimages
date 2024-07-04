# sf_dalleimages

## Introduction
TYPO3 Extension for adding AI generating images to content elements with Dalle-E

## Warning
Be sure to not use any stylistics, artworks or artists presets that can cause copyright issues for commercial deployment.

## Technologies
- PHP 8.1 - 8.3
- Bootstrap 5

## Installation

- require stackfactory/sf_dalleimages in when running TYPO3 in composer mode
- or install with extension manager
- clear caches

## Setup 
This extension requires OpenAi Platform API Key. </br>
The Key can be setted in Typoscript Constant.</br> 

plugin.tx_sf_dalleimages.dalleApiKey = 

## Features
- generating prompts dynamically with multiple category presets
- saves data as "asset" in tt_content like textmedia, image, etc 
- real time generation via ajax request

![grafik](https://github.com/akaufhold/sf_dalleimages/assets/27824413/bfc64ede-093b-4fb1-9e8a-583cbcfc389e)
![ezgif-6-23c3a03653](https://github.com/akaufhold/sf_dalleimages/assets/27824413/81252f1d-0816-4f20-9384-1900a1144f93)

## Todos
- support for other image generating ai's (Midjourney, getimg.ai, stability.ai and others)
- autocomplete subject with all words from english dictionary (https://github.com/dwyl/english-words)
