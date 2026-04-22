import React from "react";
import ReactDOM from "react-dom/client";
import Timer from "./Timer";
import { PictureInPicture2 } from "lucide-react"

export default function TimerPip({ defaultDuration = 60 }) {

  async function openPip() {
    try {
      const dpip = await window.documentPictureInPicture.requestWindow({
        width: 200,
        height: 200,
      });

      const pipDiv = dpip.document.createElement("div");
      pipDiv.setAttribute("id", "pip-root");
      dpip.document.body.append(pipDiv);
      const pipRoot = ReactDOM.createRoot(
        dpip.document.getElementById("pip-root")
      );
      
      
      pipRoot.render(
        <Timer
          isPiP
          defaultDuration={defaultDuration}
        />
      );

      // styles with tailwind
      const script = dpip.document.createElement("script");
      script.src = "https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4";
      dpip.document.head.appendChild(script);

    } catch (error) {
      console.error("Error opening PiP:", error);
    }
  }

  return (
    <PictureInPicture2
      size={30}
      className="text-gray-700 cursor-pointer"
      onClick={() => openPip()} />
  );
}
