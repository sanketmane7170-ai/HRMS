import { execSync } from 'child_process';

try {
  console.log('Running fresh migrations...');
  execSync('php artisan migrate:fresh --force', { stdio: 'inherit' });
  console.log('Fresh migrations completed successfully.');

  console.log('Seeding database...');
  execSync('php artisan db:seed --class=DatabaseSeeder --force', { stdio: 'inherit' });
  console.log('Database seeding completed successfully.');

  console.log('Seeding modules: Leave, Attendance, Announcement, Asset, Document...');
  execSync('php artisan module:seed Leave --force', { stdio: 'inherit' });
  execSync('php artisan module:seed Attendance --force', { stdio: 'inherit' });
  execSync('php artisan module:seed Announcement --force', { stdio: 'inherit' });
  execSync('php artisan module:seed Asset --force', { stdio: 'inherit' });
  execSync('php artisan module:seed Document --force', { stdio: 'inherit' });

  console.log('Module seeding completed successfully.');
} catch (error) {
  console.error('Error occurred:', error.message);
}
