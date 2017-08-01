#!/usr/bin/env ruby
#
# Ruby script for collecting several marc XML files in one marc binary file
#

require 'marc'
require 'logger'
require 'fileutils'

start_time = Time.new

MARC::ControlField.control_tags.add("992")
puts "using XML library: " + MARC::XMLReader.best_available!

dir = ARGV[0]
Dir.mkdir dir + '/collected' unless File.exists? dir + 'collected'
Dir.mkdir dir + '/errors' unless File.exists? dir + 'errors'
Dir.mkdir dir + '/log' unless File.exists? dir + 'log'
output_file_name = 'collect_' + Time.new.strftime("%Y-%m-%d_%H-%M-%S");
output_file = dir + output_file_name + '.mrc'
logger = Logger.new(dir + 'log/' + output_file_name + ".log")
writer = MARC::Writer.new(output_file)
writer.allow_oversized = true

count = 0

Dir.glob(dir + '*.xml') do |xml_file|
	puts "reading #{xml_file}"
	begin
		reader = MARC::XMLReader.new(xml_file)
		reader.each do |r|
			# fix leader if it is not exactly 24 bytes long
			if r.leader.length != 24
				r.leader = r.leader.ljust(24,"0")
				r.leader = r.leader[0,24]

				r.append(MARC::DataField.new('024', '7',  ' ', ['a', r['001'].value], ['2', 'iDAI.bibliography']))
				r['001'].value = 'DAI-' + r['001'].value

				if r['003']
					r['003'] = 'ZENON'
				else
					r.append(MARC::ControlField.new('003', 'ZENON'))
			        end

				msg = "Warning: Invalid leader length in #{xml_file}, fixed on the fly"
		    	puts msg
		    	logger.error msg
			end
			writer.write r
			FileUtils.mv(xml_file, dir + '/collected/')
			count += 1
		end
	rescue => e
		FileUtils.mv(xml_file, dir + '/errors/')
    	msg = "Error while processing #{xml_file}: #{e.message}"
    	puts msg
    	logger.error msg
	end
end

writer.close()
puts "finished collecting MARC XML files into #{output_file}"

end_time = Time.new
duration = end_time - start_time
seconds = duration % 60
minutes = (duration / 60) % 60
hours = duration / (60 * 60)
avg = count / duration
puts "processed #{count} records in #{format("%dh %dm %ds", hours, minutes, seconds)} (avg: #{avg.round(3)} records per second)"
