require 'marc'


name = File.basename(ARGV[0], File.extname(ARGV[0]))



# reading records from a batch file
reader = MARC::Reader.new(ARGV[0], :external_encoding => "UTF-8")
writer = MARC::Writer.new(name+'_preprocessed.mrc')
error_writer  = MARC::Writer.new(name+'_error.mrc')
for record in reader
  if(record['001'])
    record['001'].value = 'DAI-' + record['001'].value
    writer.write(record)
  else
    error_writer.write(record)
  end
end

error_writer.close()
writer.close()
reader.close()
