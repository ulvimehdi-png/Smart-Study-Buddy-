import React from 'react';
import { Home, ArrowLeft } from 'lucide-react';
import { AnimatedBlobs, GradientButton } from '../components/shared';
import NotFoundImage from '../assets/404-integrated.png';

export default function NotFound() {
  return (
    <div className="min-h-screen flex items-center justify-center relative px-4 py-20">
      {/* Animated Background */}
      <AnimatedBlobs />

      <div className="max-w-2xl mx-auto text-center relative z-10 mt-8">
        <div className="flex justify-center mb-8">
          <img 
            src={NotFoundImage} 
            alt="Giant 404 sign crashed on a desk" 
            className="w-full max-w-lg h-auto object-contain drop-shadow-2xl hover:scale-105 hover:rotate-5 transition-transform duration-500 ease-out rounded-3xl"
          />
        </div>
        
        <h2 className="text-4xl font-bold mb-6 text-text-main">
          Lost in the Study Zone?
        </h2>

        <p className="text-xl text-text-secondary mb-12 max-w-2xl mx-auto">
          We can't seem to find the page you're looking for. It might have been moved, deleted, or crushed by a giant glowing sign.
        </p>

        <div className="flex flex-col sm:flex-row gap-4 justify-center items-center">
          <GradientButton to="/" icon={Home} size="lg">
            Back to Home
          </GradientButton>
          
          <GradientButton 
            to="#" 
            onClick={(e) => { e.preventDefault(); window.history.back(); }} 
            icon={ArrowLeft} 
            variant="secondary" 
            size="lg"
          >
            Go Back
          </GradientButton>
        </div>
      </div>
    </div>
  );
}
