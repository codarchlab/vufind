#!/usr/bin/env ruby
#
# Ruby script for aleph sequential to marc binary conversion
#

require 'marc'
require 'marc_alephsequential'
require 'pp'


name = File.basename(ARGV[0], File.extname(ARGV[0]))
reader = MARC::AlephSequential::Reader.new(ARGV[0])
Dir.mkdir "mrc" unless File.exists? "mrc"
writer = MARC::Writer.new("mrc/" + name + ".mrc")
Dir.mkdir "log" unless File.exists? "log"
logger = Logger.new("log/" + name + ".log")

begin
	reader.each do |r|
		begin
			# filter out duplicate thesaurus fields
			r.find_all { |f| f.tag =~ /^T../ }.each do |f|
				r.fields.delete f
			end
    		writer.write r
    	rescue => e
    		logger.error "Error while writing record #{r['001']} to MARC: #{e.message}"
    	end
	end  
rescue MARC::AlephSequential::Error => e
	logger.error "Error while parsing record #{e.record_id} at/near #{e.line_number}: #{e.message}"
    retry # may or may not work the way you'd hope/expect
rescue => e
	logger.error "Other error of some sort. quitting. #{e.message}"
end

writer.close()
puts "finished"