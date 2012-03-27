#!/usr/bin/env python
# encoding: utf-8
"""
google_marketplace_to_cse_message.py

Created by Michael Smith on 2012-03-26.
Copyright (c) 2012 True Action Network.

The MIT License
Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
"""
##
# Steps to a demo product:
#
# ✓. Read in static test data and write out a StringIO containing that data in avro.
# ✓. Publish the avro test data to the fabric at /cse/offer/create and get a 200 back.
# 3. Process the response data.
# 4. Get the schema from a URL.

from StringIO import StringIO
from avro import schema, io, datafile
from testdata import TESTDATA, BEARER
from httplib2 import Http

HTTP = Http()

publish_url = 'https://api.sandbox.x.com/fabric/cse/offer/create'
SCHEMA = schema.parse(open('/home/msmith/dev/Sample-capabilities/Comparison_Shopping_Engine/provider/langs/php/cse.avpr', 'r').read())

##
# Write the message data to a StringIO
#
# @return StringIO
#
def write_data():
	message = TESTDATA
	datum_writer = io.DatumWriter(SCHEMA)
	data = StringIO()
	datafile_writer = datafile.DataFileWriter(
		data,
		datum_writer,
		writers_schema=SCHEMA,
		codec='deflate',
	)
	datafile_writer.append(message)
	return data

##
# Make the POST to x.com and dump its response
#
def main():
	headers = {
		"Content-Type": "avro/binary",
		"Authorization": "Bearer %s" % BEARER,
		"X-XC-SCHEMA-VERSION": "1.0.0",
	}
	body = write_data().getvalue()
	resp, content = HTTP.request(
		uri=publish_url,
		method='POST',
		body=body,
		headers=headers,
	)
	print resp, content

if '__main__' == __name__:
	main()
