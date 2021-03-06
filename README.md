Sample Capabilities for XFabric
=============

This repository contains list of sample capabilities that are intended to serve as examples for any developers who wish to write capabilities for the X.commerce platform.

You can find more details about the individual capabilities by navigating to the respective folders.

Examples
-------
1. Comparison shopping engine
   * Written in PHP. Utilizes the message contract from https://github.com/xcommerce/X.commerce-Contracts/blob/master/comparisonshoppingengine/src/main/avro/ComparisonShoppingEngine.avdl to simulate a comparison shopping engine. This code uses Google's commerce search API to publish a feed to a comparison shopping engine registered with X.commerce.

2. Auctionhouse Bidder
   * This example is written in Java and was part of the Innovate 2011 workshop and illustrates an auctionhouse. The XFabric message flow is illustrated using bidding messages for any particular item.

3. Innovate developer demo
   * This was presented as the "[From code to capability session](http://www.youtube.com/watch?feature=player_profilepage&v=8fZPtLvApvI)" at Innovate 2011. It's a  combination of a Magento extension that listens to inventory updates and publishes them to the Fabric, plus a Ruby on Rails application that receives the updates and posts to a user's Facebook wall with a "deal of the day".


Contributing
------------

1. Fork it.
2. Add your capability. Commit your changes (`git commit -am "Added Awesomeness"`)
3. Submit a pull request
4. Create an [Issue][1] with a link to your branch
