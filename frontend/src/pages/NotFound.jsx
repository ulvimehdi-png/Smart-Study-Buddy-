import React from 'react';
import { Home, ArrowLeft } from 'lucide-react';
import { AnimatedBlobs, GradientButton } from '../components/shared';
import NotFoundImage from '../assets/404-integrated.png';
import { useNavigate } from 'react-router-dom';

export default function NotFound() {
  const navigate = useNavigate();

  return (
    <div className="min-h-screen flex items-center justify-center relative px-4 py-20">
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
          
          <button 
            onClick={() => navigate(-1)}
            className="inline-flex items-center justify-center gap-2 rounded-xl bg-transparent border border-border-base px-6 py-3 font-medium text-text-main transition hover:bg-bg-elevated cursor-pointer"
          >
            <ArrowLeft className="h-5 w-5" aria-hidden="true" />
            Go Back
          </button>
        </div>
      </div>
    </div>
  );
}