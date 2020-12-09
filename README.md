**Since the IONOS Address Book feature no longer meets our current technical requirements, we will be discontinuing the feature on 02/16/2021.**

**Before this date, you will still be able to [export your contacts as a vCard (.VCF file)](https://www.ionos.com/help/index.php?id=3862) for your own backup or to transfer them to another platform.**

# wp-cosy-address-book

Sends visitor contact data generated by contact forms embedded in WordPress websites to IONOS Address Book
Current supported contact form types are: **Contact Form 7** and **WPforms Lite**

## Use Cases

Main CoSy Address Book Use Cases, whereas WordPress CoSy Plugin is **"Third party App"**

![Alt text](images/cosy-use-cases-sequence.png?raw=true "WordPress CoSy Address Book Use Cases")

## How to start application

- Just start with ```docker-compose up```
- To be able to use environment stage values add `.env` file to root directory and add parameter `STAGE_ENV` with you preferred value
- Currently are following stage environments supported: development, integration and production, whereas 'production' is default value

## How to use application

- Make sure one of supported contact form plugins enumerated above is activated and installed in your WordPress installation
- Generate a new API key in IONOS Address Book Frontend settings page (you can skip this step in development stage)
- Go to your IONOS Address Book WordPress admin menu page, paste and save generated value (you can type any value on development stage)
- Depending on activate contact form plugin, you might need to configure mapping between contact form fields and IONOS Address Book API fields

## How to build project dependencies

- Install vendor dependencies
```
composer install
```

- Install local dependencies
```
npm install
```

- Compile the _LESS_ declarations into the CSS styles and generate the CSS files:

```
npm run run-grunt build-styles
```

- Build/Update the *.pot languages template file and all the *.mo languages binary files:
```
npm run run-grunt build-i18n
```
- Build all:
```
npm run run-grunt
```

## How to run unit tests
```
docker-compose --file=docker-compose-test.yml up
```