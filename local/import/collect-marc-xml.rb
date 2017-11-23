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
output_file = dir + output_file_name + '.xml'
logger = Logger.new(dir + 'log/' + output_file_name + "_aggregation.log")
writer = MARC::XMLWriter.new(output_file)

count = 0

def split_language_keys(record)

	if record['041'] and record['041']['a'] and record['041']['b']
		main_language_key_length = record['041']['a'].length
		if record['041']['b'].kind_of?(String) and
				record['041']['b'].length % main_language_key_length == 0 and
				record['041']['b'].length != main_language_key_length

			split_b = record['041']['b'].scan(/.{#{main_language_key_length}}/)
			updated_field = MARC::DataField.new('041',record['indiciator1'],record['indiciator2'])

			# copy all subfields except 'b' from original
			record['041'].each do |s|
				if(s.code != 'b')
					updated_field.append(s)
				end
			end

			# build new subfields 'b' from split values
			split_b.each do |value|
				updated_field.append(MARC::Subfield.new('b',value))
			end

			record.fields.delete(record['041'])
			record.append(updated_field)
		end
	end

	record
end

Dir[dir + '*.xml'].reject { |xml_file| xml_file == output_file }.each do |xml_file|
	begin
		reader = MARC::XMLReader.new(xml_file, :external_encoding => "UTF-8")
		for record in reader
			# fix leader if it is not exactly 24 bytes long
			if record.leader.length != 24
				record.leader = record.leader.ljust(24,"0")
				record.leader = record.leader[0,24]

				msg = "Invalid leader length in #{xml_file}, fixed on the fly"
				logger.warn msg
			end

			if record['003']
				record.fields.delete(record['003'])
			end
			record.append(MARC::ControlField.new('003', 'DE-2553'))

			record = split_language_keys(record)

			writer.write record
			FileUtils.mv(xml_file, dir + '/collected/')
			count += 1
		end
	rescue => e
		FileUtils.mv(xml_file, dir + '/errors/')
    	msg = "Error while processing #{xml_file}: #{e.message}"
    	puts msg
    	logger.error msg
    	puts e.backtrace
    	logger.error e.backtrace
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
