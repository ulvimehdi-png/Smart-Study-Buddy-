import React, { useEffect, useState } from "react";
import { createPortal } from "react-dom";
import Timer from "./Timer";
import { PictureInPicture2 } from "lucide-react"
import TimerButton from "./Button/TimerButton";

export default function TimerPip({className = ""}) {
  const [pipWindow, setPipWindow] = useState(null);
  const [pipContainer, setPipContainer] = useState(null);

  useEffect(() => {
    if (!pipWindow) {
      return;
    }

    function handlePageHide() {
      setPipContainer(null);
      setPipWindow(null);
    }

    pipWindow.addEventListener("pagehide", handlePageHide);

    return () => {
      pipWindow.removeEventListener("pagehide", handlePageHide);
    };
  }, [pipWindow]);

  async function openPip() {
    try {
      if (pipWindow && !pipWindow.closed) {
        pipWindow.focus();
        return;
      }

      const nextPipWindow = await window.documentPictureInPicture.requestWindow({
        width: 350,
        height: 350,
      });

      // 1. Inject Tailwind
      const tw = nextPipWindow.document.createElement("script");
      tw.src = "https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4";
      nextPipWindow.document.head.appendChild(tw);

      // 2. Inject Progressbar CSS (Required because the library is in a separate document)
      const style = nextPipWindow.document.createElement("style");
      style.textContent = `
        .CircularProgressbar { width: 100%; vertical-align: middle; }
        .CircularProgressbar-path { stroke: #be123c; stroke-linecap: round; }
        .CircularProgressbar-trail { stroke: #f3f4f6; }
      `;
      nextPipWindow.document.head.appendChild(style);

      const pipDiv = nextPipWindow.document.createElement("div");
      pipDiv.setAttribute("id", "pip-root");
      nextPipWindow.document.body.append(pipDiv);
      setPipWindow(nextPipWindow);
      setPipContainer(pipDiv);

      // styles with tailwind
      const script = nextPipWindow.document.createElement("script");
      script.src = "https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4";
      nextPipWindow.document.head.appendChild(script);

    } catch (error) {
      console.error("Error opening PiP:", error);
    }
  }

  return (
    <>
      <TimerButton isPiP={false} onClick={openPip} IconComp={PictureInPicture2} className={className}>
        PiP
      </TimerButton>
      {pipContainer && createPortal(<Timer isPiP />, pipContainer)}
    </>
  );
}
