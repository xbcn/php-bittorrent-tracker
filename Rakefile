require 'date'
require 'digest/md5'
require 'fileutils'

basedir = "."
build   = "#{basedir}/build"
source  = "#{basedir}/PHP"

desc "Task used by Jenkins-CI"
task :jenkins => [:lint, :prepare, :installdep, :phpunit, :phpdoc, :phploc, :phpcs_ci, :phpcb, :phpcpd, :phpmd, :phpmd_html]

desc "Task used by Travis-CI"
task :travis => [:installdep, :phpunit]

desc "Default task"
task :default => [:lint, :prepare, :installdep, :phpunit, :phpdoc, :phpcs, :phpcb]

desc "Clean up and create artifact directories"
task :prepare do
  puts "Prepare build"

  FileUtils.rm_rf build
  FileUtils.mkdir build

  ["coverage", "logs", "docs", "code-browser"].each do |d|
    FileUtils.mkdir "#{build}/#{d}"
  end
end

desc "Check syntax on all php files in the project"
task :lint do
  puts "lint PHP files"

  `git ls-files "*.php"`.split("\n").each do |f|
    begin
      sh %{php -l #{f}}
    rescue Exception
      exit 1
    end
  end
end

desc "Install dependencies"
task :installdep do
  if ENV["TRAVIS"] == "true"
    system "composer --no-ansi install --dev"
  else
    Rake::Task["install_composer"].invoke
    system "php -d \"apc.enable_cli=0\" composer.phar install --dev"
  end
end

desc "Update dependencies"
task :updatedep do
  Rake::Task["install_composer"].invoke
  system "php -d \"apc.enable_cli=0\" composer.phar update --dev"
end

desc "Install/update composer itself"
task :install_composer do
  if File.exists?("composer.phar")
    system "php -d \"apc.enable_cli=0\" composer.phar self-update"
  else
    system "curl -s http://getcomposer.org/installer | php -d \"apc.enable_cli=0\""
  end
end

desc "Run unit tests"
task :phpunit do
  config = "phpunit.xml.dist"

  if ENV["TRAVIS"] == "true"
    config = "phpunit.xml.travis"
  elsif File.exists?("phpunit.xml")
    config = "phpunit.xml"
  end

  begin
    sh %{vendor/bin/phpunit --verbose -c #{config}}
  rescue Exception
    exit 1
  end
end

desc "Generate API documentation using phpdoc"
task :phpdoc do
  puts "Generate API docs"
  system "phpdoc -d #{source} -t #{build}/docs --title \"PHP BitTorrent Tracker API Documentation\""
end

desc "Generate phploc logs"
task :phploc do
  puts "Generate LOC data"
  system "phploc --log-csv #{build}/logs/phploc.csv --log-xml #{build}/logs/phploc.xml #{source}"
end

desc "Generate checkstyle.xml using PHP_CodeSniffer"
task :phpcs_ci do
  puts "Generate CS violation reports"
  system "phpcs --report=checkstyle --report-file=#{build}/logs/checkstyle.xml --standard=Imbo #{source}"
end

desc "Check CS"
task :phpcs do
  puts "Check CS"
  system "phpcs --standard=Imbo #{source}"
end

desc "Aggregate tool output with PHP_CodeBrowser"
task :phpcb do
  puts "Generate codebrowser"
  system "phpcb --source #{source} --output #{build}/code-browser"
end

desc "Generate pmd-cpd.xml using PHPCPD"
task :phpcpd do
  puts "Generate CPD logs"
  system "phpcpd --log-pmd #{build}/logs/pmd-cpd.xml #{source}"
end

desc "Generate pmd.xml using PHPMD (configuration in phpmd.xml)"
task :phpmd do
  puts "Generate mess detection logs"
  system "phpmd #{source} xml #{basedir}/phpmd.xml --reportfile #{build}/logs/pmd.xml"
end

desc "Generate pmd.html using PHPMD (configuration in phpmd.xml)"
task :phpmd_html do
  puts "Generate mess detection HTML"
  system "phpmd #{source} html #{basedir}/phpmd.xml --reportfile #{build}/logs/pmd.html"
end

desc "Release a new version"
task :release, :version do |t, args|
  puts "Release a new version of the library"
  version = args[:version]

  if /^[\d]+\.[\d]+\.[\d]+$/ =~ version
    # Execute the default task first (tests and so forth)
    Rake::Task["default"].invoke

    # Checkout the master branch
    system "git checkout master"

    # Merge in the current state of the develop branch
    system "git merge develop"

    # Tag release and push
    system "git tag #{version}"
    system "git push"
    system "git push --tags"
    system "git checkout develop"
  else
    puts "'#{version}' is not a valid version"
    exit 1
  end
end
